<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Events\StockLevelLow;
use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Repositories\Contracts\StockRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * StockService is the ONLY thing in the application allowed to change
 * stock_levels.quantity or write a stock_movements row. Every other module
 * (purchases, POS, refunds, adjustments, transfers) calls into this class
 * rather than touching those tables directly.
 *
 * Why centralize this: the invariant "stock_levels.quantity always equals
 * SUM(stock_movements.quantity) for that product+warehouse" is the whole
 * point of the ledger design from Phase 1. If five different services each
 * had their own "decrement stock" code path, that invariant would only be
 * as strong as the most careless one. One class, one transaction pattern,
 * one place to audit.
 *
 * Concurrency: every mutation locks the stock_levels row
 * (SELECT ... FOR UPDATE via StockRepository::lockForUpdate) inside a DB
 * transaction before reading the current quantity. This is what makes two
 * near-simultaneous decrements (e.g. an online sale and an offline sale
 * syncing in the same second) serialize correctly instead of both reading
 * the same stale quantity and silently losing one decrement (a classic
 * lost-update race condition).
 */
class StockService
{
    public function __construct(
        private readonly StockRepositoryInterface $stockRepository,
    ) {}

    /**
     * Increment stock — purchases received, sale cancellations/refunds
     * restoring stock, positive adjustments, transfer-in. Never blocks.
     */
    public function increment(
        Product $product,
        int $warehouseId,
        int $quantity,
        StockMovementType $type,
        ?User $user = null,
        ?string $note = null,
        ?object $reference = null,
        bool $isFromOfflineSync = false,
    ): StockMovement {
        if (!$type->isInbound()) {
            throw new \InvalidArgumentException("{$type->value} is not an inbound movement type.");
        }

        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Increment quantity must be positive.');
        }

        return DB::transaction(function () use ($product, $warehouseId, $quantity, $type, $user, $note, $reference, $isFromOfflineSync) {
            $stockLevel = $this->stockRepository->lockForUpdate($product->id, $warehouseId);

            $before = $stockLevel->quantity;
            $after = $before + $quantity;

            $this->stockRepository->setQuantity($stockLevel, $after);

            return $this->writeMovement($product, $warehouseId, $quantity, $before, $after, $type, $user, $note, $reference, $isFromOfflineSync);
        });
    }

    /**
     * Decrement stock for an ONLINE/live operation (POS sale rung up with
     * connectivity, manual stock-out adjustment). Throws
     * InsufficientStockException if it would take stock negative — for a
     * live transaction we CAN and should stop the cashier before
     * overselling, since nothing has physically left the store yet.
     */
    public function decrement(
        Product $product,
        int $warehouseId,
        int $quantity,
        StockMovementType $type,
        ?User $user = null,
        ?string $note = null,
        ?object $reference = null,
    ): StockMovement {
        if ($type->isInbound()) {
            throw new \InvalidArgumentException("{$type->value} is not an outbound movement type.");
        }

        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Decrement quantity must be positive.');
        }

        return DB::transaction(function () use ($product, $warehouseId, $quantity, $type, $user, $note, $reference) {
            $stockLevel = $this->stockRepository->lockForUpdate($product->id, $warehouseId);

            $before = $stockLevel->quantity;
            $after = $before - $quantity;

            if ($product->track_stock && $after < 0) {
                throw new InsufficientStockException($product->id, $warehouseId, $quantity, $before);
            }

            $this->stockRepository->setQuantity($stockLevel, $after);

            $movement = $this->writeMovement(
                $product,
                $warehouseId,
                -$quantity,
                $before,
                $after,
                $type,
                $user,
                $note,
                $reference,
                false
            );

            $this->maybeFireLowStock($product, $warehouseId, $after);

            return $movement;
        });
    }

    /**
     * Decrement stock for an OFFLINE-SYNCED sale. This is the one path in
     * the system that is allowed to push stock negative — per product
     * requirement, a sale that already physically happened at the register
     * must be accepted, not rejected after the fact just because the server
     * now disagrees about what was on the shelf. The resulting negative
     * balance is exactly the signal that something needs reconciling, and
     * SaleSyncService logs a pos_sync_audits row whenever this happens so
     * it surfaces in the back office instead of being silently absorbed.
     */
    public function decrementAllowingNegative(
        Product $product,
        int $warehouseId,
        int $quantity,
        ?User $user = null,
        ?string $note = null,
        ?object $reference = null,
    ): StockMovement {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Decrement quantity must be positive.');
        }

        return DB::transaction(function () use ($product, $warehouseId, $quantity, $user, $note, $reference) {
            $stockLevel = $this->stockRepository->lockForUpdate($product->id, $warehouseId);

            $before = $stockLevel->quantity;
            $after = $before - $quantity;

            $this->stockRepository->setQuantity($stockLevel, $after);

            $movement = $this->writeMovement(
                $product,
                $warehouseId,
                -$quantity,
                $before,
                $after,
                StockMovementType::SaleOut,
                $user,
                $note,
                $reference,
                true
            );

            $this->maybeFireLowStock($product, $warehouseId, $after);

            return $movement;
        });
    }

    /**
     * Set stock to an exact counted quantity (stock-take / adjustment).
     * Internally computes the delta and writes it as adjustment_in or
     * adjustment_out so the ledger still records a directional movement
     * rather than a "teleport" with no inferable sign.
     */
    public function setAbsoluteQuantity(
        Product $product,
        int $warehouseId,
        int $countedQuantity,
        User $user,
        ?string $note = null,
        ?object $reference = null,
    ): ?StockMovement {
        return DB::transaction(function () use ($product, $warehouseId, $countedQuantity, $user, $note, $reference) {
            $stockLevel = $this->stockRepository->lockForUpdate($product->id, $warehouseId);
            $before = $stockLevel->quantity;
            $delta = $countedQuantity - $before;

            if ($delta === 0) {
                return null; // no-op, nothing to record
            }

            $this->stockRepository->setQuantity($stockLevel, $countedQuantity);

            $type = $delta > 0 ? StockMovementType::AdjustmentIn : StockMovementType::AdjustmentOut;

            $movement = $this->writeMovement(
                $product,
                $warehouseId,
                $delta,
                $before,
                $countedQuantity,
                $type,
                $user,
                $note,
                $reference,
                false
            );

            if ($delta < 0) {
                $this->maybeFireLowStock($product, $warehouseId, $countedQuantity);
            }

            return $movement;
        });
    }

    public function currentQuantity(Product $product, int $warehouseId): int
    {
        return $this->stockRepository->getQuantity($product->id, $warehouseId);
    }

    private function writeMovement(
        Product $product,
        int $warehouseId,
        int $signedQuantity,
        int $before,
        int $after,
        StockMovementType $type,
        ?User $user,
        ?string $note,
        ?object $reference,
        bool $isFromOfflineSync,
    ): StockMovement {
        return $this->stockRepository->recordMovement([
            'product_id' => $product->id,
            'warehouse_id' => $warehouseId,
            'quantity' => $signedQuantity,
            'quantity_before' => $before,
            'quantity_after' => $after,
            'type' => $type,
            'reference_type' => $reference ? $reference::class : null,
            'reference_id' => $reference?->id,
            'user_id' => $user?->id,
            'note' => $note,
            'is_from_offline_sync' => $isFromOfflineSync,
        ]);
    }

    private function maybeFireLowStock(Product $product, int $warehouseId, int $newQuantity): void
    {
        if ($product->track_stock && $newQuantity <= $product->min_stock_level) {
            event(new StockLevelLow($product, $warehouseId, $newQuantity));
        }
    }
}
