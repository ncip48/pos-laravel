<?php

namespace App\Support;

use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Support\Str;

/**
 * Generates a human-scannable SKU when the user doesn't supply one manually.
 * Format: {CATEGORY-PREFIX}-{RANDOM4}, e.g. "BEV-X7K2". Collision-checked
 * against the repository and retried rather than relying on randomness
 * alone — SKUs are unique-indexed at the DB level too, so this is a
 * best-effort UX nicety, not the sole integrity guarantee.
 */
class SkuGenerator
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
    ) {}

    public function generate(?string $categoryName = null): string
    {
        $prefix = $categoryName
            ? strtoupper(Str::limit(preg_replace('/[^A-Za-z]/', '', $categoryName), 3, ''))
            : 'GEN';

        $prefix = $prefix !== '' ? $prefix : 'GEN';

        do {
            $sku = $prefix . '-' . strtoupper(Str::random(4));
        } while ($this->productRepository->skuExists($sku));

        return $sku;
    }
}
