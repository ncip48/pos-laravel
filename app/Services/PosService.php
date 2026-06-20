<?php

namespace App\Services;

use App\Enums\DiscountType;
use App\Enums\StockMovementType;
use App\Events\SaleCompleted;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Register;
use App\Models\Sale;
use App\Models\User;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Handles checkout for an ONLINE POS sale -- the cashier has connectivity
 * right now, so the server prices everything live from the current catalog
 * and can safely reject the sale if stock is insufficient (nothing has
 * physically left the store yet, so blocking is the correct, safe choice).
 *
 * This is distinct from SaleSyncService, which ingests sales that already
 * happened offline -- those use locked client-side prices and never block
 * on stock. Keeping the two paths in separate classes (rather than one
 * "checkout" method with an offline flag threaded through) keeps each
 * method's invariants simple to read and test in isolation.
 */
class PosService
{
    public function __construct(
        private readonly SaleRepositoryInterface $saleRepository,
        private readonly StockService $stockService,
    ) {}

    /**
     * @param array<int, array{product_id:int, quantity:int, discount_type?:string, discount_value?:float}> $cartItems
     * @param array<int, array{method:string, amount:float, reference_number?:string}> $payments
     */
    public function checkout(
        array $cartItems,
        array $payments,
        int $warehouseId,
        User $cashier,
        ?Register $register = null,
        ?int $customerId = null,
        ?string $discountType = null,
        ?float $discountValue = null,
        ?string $clientUuid = null,
    ): Sale {
        if (empty($cartItems)) {
            throw new InvalidArgumentException('Cart is empty.');
        }

        $clientUuid ??= (string) \Illuminate\Support\Str::uuid();

        // Defensive idempotency check even for the "online" path: a
        // double-submitted checkout click (slow network, double-tap) must
        // not create two sales for the same cart.
        if ($existing = $this->saleRepository->findByClientUuid($clientUuid)) {
            return $existing;
        }

        return DB::transaction(function () use (
            $cartItems,
            $payments,
            $warehouseId,
            $cashier,
            $register,
            $customerId,
            $discountType,
            $discountValue,
            $clientUuid
        ) {
            $products = Product::whereIn('id', array_column($cartItems, 'product_id'))
                ->get()
                ->keyBy('id');

            $lineItems = [];
            $subtotalCents = 0;
            $taxCents = 0;

            foreach ($cartItems as $cartItem) {
                $product = $products->get($cartItem['product_id']);

                if (!$product) {
                    throw new InvalidArgumentException("Product #{$cartItem['product_id']} not found.");
                }

                $quantity = (int) $cartItem['quantity'];
                $unitPrice = $product->selling_price_cents;
                $lineSubtotal = $unitPrice * $quantity;

                $lineDiscount = $this->calculateDiscount(
                    $lineSubtotal,
                    $cartItem['discount_type'] ?? null,
                    $cartItem['discount_value'] ?? null,
                );

                $taxableAmount = $lineSubtotal - $lineDiscount;
                $taxRate = (float) ($product->tax_rate_percent ?? 0);
                $lineTax = $product->is_tax_inclusive_price
                    ? 0 // tax-inclusive pricing: tax is already part of unit_price, not added on top
                    : (int) round($taxableAmount * ($taxRate / 100));

                $lineTotal = $taxableAmount + $lineTax;

                $lineItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price_cents' => $unitPrice,
                    'unit_cost_cents' => $product->cost_price_cents,
                    'discount_cents' => $lineDiscount,
                    'discount_type' => $cartItem['discount_type'] ?? null,
                    'discount_value' => $cartItem['discount_value'] ?? 0,
                    'tax_cents' => $lineTax,
                    'tax_rate_percent' => $taxRate,
                    'subtotal_cents' => $lineSubtotal,
                    'total_cents' => $lineTotal,
                ];

                $subtotalCents += $lineSubtotal;
                $taxCents += $lineTax;
            }

            $orderDiscountCents = $this->calculateDiscount($subtotalCents, $discountType, $discountValue);
            $totalCents = max(0, $subtotalCents - $orderDiscountCents + $taxCents);

            $paidCents = 0;
            foreach ($payments as $payment) {
                $paidCents += Money::fromUnits($payment['amount'])->amount();
            }

            if ($paidCents < $totalCents) {
                throw new InvalidArgumentException(
                    'Insufficient payment: total is ' . Money::fromAmount($totalCents)->formatted() .
                        ', received ' . Money::fromAmount($paidCents)->formatted() . '.'
                );
            }

            $changeCents = $paidCents - $totalCents;

            $sale = Sale::create([
                'invoice_number' => $this->saleRepository->nextInvoiceNumber(),
                'client_uuid' => $clientUuid,
                'customer_id' => $customerId ?? Customer::guest()->id,
                'warehouse_id' => $warehouseId,
                'register_id' => $register?->id,
                'user_id' => $cashier->id,
                'status' => 'completed',
                'subtotal_cents' => $subtotalCents,
                'discount_cents' => $orderDiscountCents,
                'discount_type' => $discountType,
                'discount_value' => $discountValue ?? 0,
                'tax_cents' => $taxCents,
                'tax_rate_percent' => $subtotalCents > 0 ? round(($taxCents / $subtotalCents) * 100, 2) : 0,
                'total_cents' => $totalCents,
                'paid_cents' => $paidCents,
                'change_cents' => $changeCents,
                'was_created_offline' => false,
                'synced_at' => now(),
            ]);

            foreach ($lineItems as $line) {
                $sale->items()->create([
                    'product_id' => $line['product']->id,
                    'product_name_snapshot' => $line['product']->name,
                    'product_sku_snapshot' => $line['product']->sku,
                    'quantity' => $line['quantity'],
                    'unit_price_cents' => $line['unit_price_cents'],
                    'unit_cost_cents' => $line['unit_cost_cents'],
                    'discount_cents' => $line['discount_cents'],
                    'discount_type' => $line['discount_type'],
                    'discount_value' => $line['discount_value'],
                    'tax_cents' => $line['tax_cents'],
                    'tax_rate_percent' => $line['tax_rate_percent'],
                    'subtotal_cents' => $line['subtotal_cents'],
                    'total_cents' => $line['total_cents'],
                ]);

                if ($line['product']->track_stock) {
                    $this->stockService->decrement(
                        product: $line['product'],
                        warehouseId: $warehouseId,
                        quantity: $line['quantity'],
                        type: StockMovementType::SaleOut,
                        user: $cashier,
                        note: "Sold via {$sale->invoice_number}",
                        reference: $sale,
                    );
                }
            }

            foreach ($payments as $payment) {
                $sale->payments()->create([
                    'method' => $payment['method'],
                    'amount_cents' => Money::fromUnits($payment['amount'])->amount(),
                    'reference_number' => $payment['reference_number'] ?? null,
                ]);
            }

            $sale->load(['items.product', 'payments', 'customer', 'cashier', 'warehouse']);

            event(new SaleCompleted($sale));

            return $sale;
        });
    }

    private function calculateDiscount(int $baseAmountCents, ?string $type, ?float $value): int
    {
        if (!$type || !$value) {
            return 0;
        }

        return match (DiscountType::from($type)) {
            DiscountType::Fixed => min($baseAmountCents, Money::fromUnits($value)->amount()),
            DiscountType::Percent => (int) round($baseAmountCents * (min(100, max(0, $value)) / 100)),
        };
    }
}
