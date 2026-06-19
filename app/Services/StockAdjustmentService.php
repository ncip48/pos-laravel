<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * StockAdjustments are recorded as a DRAFT first, then "approved" before any
 * stock actually moves -- this two-step flow gives a second set of eyes (or
 * at least a deliberate confirmation click) before a manual correction
 * changes what the system thinks is on the shelf, since these are exactly
 * the records an auditor or owner will scrutinize for shrinkage/theft.
 */
class StockAdjustmentService
{
    public function __construct(
        private readonly StockService $stockService,
    ) {}

    /**
     * @param array<int, array{product_id:int, system_quantity:int, counted_quantity:int}> $items
     */
    public function create(array $data, array $items, User $user): StockAdjustment
    {
        if (empty($items)) {
            throw new InvalidArgumentException('A stock adjustment must have at least one item.');
        }

        return DB::transaction(function () use ($data, $items, $user) {
            $adjustment = StockAdjustment::create([
                'adjustment_number' => $this->nextAdjustmentNumber(),
                'warehouse_id' => $data['warehouse_id'],
                'user_id' => $user->id,
                'reason' => $data['reason'],
                'notes' => $data['notes'] ?? null,
                'status' => 'draft',
            ]);

            foreach ($items as $item) {
                $adjustment->items()->create([
                    'product_id' => $item['product_id'],
                    'system_quantity' => $item['system_quantity'],
                    'counted_quantity' => $item['counted_quantity'],
                    'difference' => $item['counted_quantity'] - $item['system_quantity'],
                ]);
            }

            return $adjustment->fresh('items');
        });
    }

    /**
     * Approving is what actually triggers stock movement, via
     * StockService::setAbsoluteQuantity() for each line -- so the ledger
     * gets one adjustment_in/adjustment_out row per product reflecting the
     * real delta, traceable back to this adjustment via the polymorphic
     * reference.
     */
    public function approve(StockAdjustment $adjustment, User $approver): StockAdjustment
    {
        if ($adjustment->isApproved()) {
            throw new InvalidArgumentException('This adjustment has already been approved.');
        }

        return DB::transaction(function () use ($adjustment, $approver) {
            $adjustment->loadMissing('items.product');

            foreach ($adjustment->items as $item) {
                $this->stockService->setAbsoluteQuantity(
                    product: $item->product,
                    warehouseId: $adjustment->warehouse_id,
                    countedQuantity: $item->counted_quantity,
                    user: $approver,
                    note: "Stock adjustment {$adjustment->adjustment_number} ({$adjustment->reason})",
                    reference: $adjustment,
                );
            }

            $adjustment->update([
                'status' => 'approved',
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            return $adjustment->fresh(['items.product']);
        });
    }

    private function nextAdjustmentNumber(): string
    {
        $year = now()->year;
        $prefix = "ADJ-{$year}-";

        $last = StockAdjustment::query()
            ->where('adjustment_number', 'like', "{$prefix}%")
            ->lockForUpdate()
            ->orderByDesc('adjustment_number')
            ->first();

        $next = $last ? ((int) substr($last->adjustment_number, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
