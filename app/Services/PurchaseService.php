<?php

namespace App\Services;

use App\Enums\PurchaseStatus;
use App\Enums\StockMovementType;
use App\Models\Purchase;
use App\Models\User;
use App\Repositories\Contracts\PurchaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PurchaseService
{
    public function __construct(
        private readonly PurchaseRepositoryInterface $purchaseRepository,
        private readonly StockService $stockService,
    ) {}

    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->purchaseRepository->paginateWithFilters($filters, $perPage);
    }

    public function find(int $id): Purchase
    {
        return $this->purchaseRepository->findOrFail($id);
    }

    /**
     * Creates a purchase in 'draft' status with its line items. No stock
     * movement happens here -- stock only changes when items are marked
     * received via receiveItems(), so "on order" and "on hand" never get
     * conflated.
     *
     * @param array{supplier_id:int,warehouse_id:int,order_date:string,expected_date:?string,notes:?string} $data
     * @param array<int, array{product_id:int, quantity_ordered:int, unit_cost_cents:int}> $items
     */
    public function create(array $data, array $items, User $user): Purchase
    {
        if (empty($items)) {
            throw new InvalidArgumentException('A purchase must have at least one item.');
        }

        return DB::transaction(function () use ($data, $items, $user) {
            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += $item['quantity_ordered'] * $item['unit_cost_cents'];
            }

            $discountCents = $data['discount_cents'] ?? 0;
            $taxCents = $data['tax_cents'] ?? 0;
            $total = $subtotal - $discountCents + $taxCents;

            $purchase = $this->purchaseRepository->create([
                'purchase_number' => $this->purchaseRepository->nextPurchaseNumber(),
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'user_id' => $user->id,
                'status' => PurchaseStatus::Draft,
                'order_date' => $data['order_date'],
                'expected_date' => $data['expected_date'] ?? null,
                'subtotal_cents' => $subtotal,
                'discount_cents' => $discountCents,
                'tax_cents' => $taxCents,
                'total_cents' => max(0, $total),
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                $purchase->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity_ordered' => $item['quantity_ordered'],
                    'quantity_received' => 0,
                    'unit_cost_cents' => $item['unit_cost_cents'],
                    'subtotal_cents' => $item['quantity_ordered'] * $item['unit_cost_cents'],
                ]);
            }

            return $purchase->fresh('items');
        });
    }

    public function markOrdered(Purchase $purchase): Purchase
    {
        return $this->transitionStatus($purchase, PurchaseStatus::Ordered);
    }

    public function cancel(Purchase $purchase): Purchase
    {
        return $this->transitionStatus($purchase, PurchaseStatus::Cancelled);
    }

    /**
     * Receive some or all of a purchase's items into stock. This is the
     * ONLY place a purchase causes a stock change, and it delegates the
     * actual mutation to StockService::increment() so the ledger invariant
     * holds. Supports partial receipt -- e.g. supplier ships 8 of 10 ordered --
     * by accepting a quantity per item rather than assuming "receive everything".
     *
     * @param array<int, int> $receivedQuantities [purchase_item_id => quantity_received_now]
     */
    public function receiveItems(Purchase $purchase, array $receivedQuantities, User $user): Purchase
    {
        if (in_array($purchase->status, [PurchaseStatus::Received, PurchaseStatus::Cancelled], true)) {
            throw new InvalidArgumentException("Cannot receive items for a purchase with status '{$purchase->status->value}'.");
        }

        return DB::transaction(function () use ($purchase, $receivedQuantities, $user) {
            $purchase->loadMissing('items.product');

            foreach ($purchase->items as $item) {
                $receivingNow = $receivedQuantities[$item->id] ?? 0;

                if ($receivingNow <= 0) {
                    continue;
                }

                $maxReceivable = $item->quantityOutstanding();
                $receivingNow = min($receivingNow, $maxReceivable);

                if ($receivingNow <= 0) {
                    continue;
                }

                $item->update(['quantity_received' => $item->quantity_received + $receivingNow]);

                $this->stockService->increment(
                    product: $item->product,
                    warehouseId: $purchase->warehouse_id,
                    quantity: $receivingNow,
                    type: StockMovementType::PurchaseIn,
                    user: $user,
                    note: "Received against {$purchase->purchase_number}",
                    reference: $purchase,
                );

                // Receiving stock can change average cost going forward; this
                // system keeps it simple and uses the most recent purchase
                // cost as the product's cost_price_cents (last-cost costing,
                // not weighted-average) -- sufficient for a small/personal
                // business and avoids a separate costing-method module.
                $item->product->update(['cost_price_cents' => $item->unit_cost_cents]);
            }

            $purchase->refresh()->loadMissing('items');

            $purchase->update([
                'status' => $purchase->isFullyReceived() ? PurchaseStatus::Received : PurchaseStatus::PartiallyReceived,
                'received_at' => $purchase->isFullyReceived() ? now() : $purchase->received_at,
            ]);

            return $purchase->fresh(['items.product']);
        });
    }

    private function transitionStatus(Purchase $purchase, PurchaseStatus $next): Purchase
    {
        if (!$purchase->status->canTransitionTo($next)) {
            throw new InvalidArgumentException(
                "Cannot transition purchase from '{$purchase->status->value}' to '{$next->value}'."
            );
        }

        return $this->purchaseRepository->update($purchase, ['status' => $next]);
    }
}
