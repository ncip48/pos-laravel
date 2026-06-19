<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Support\SkuGenerator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly SkuGenerator $skuGenerator,
    ) {}

    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->paginateWithFilters($filters, $perPage);
    }

    public function find(int $id): Product
    {
        return $this->productRepository->findOrFail($id);
    }

    public function create(array $data, ?UploadedFile $image = null): Product
    {
        return DB::transaction(function () use ($data, $image) {
            $data['slug'] = $this->uniqueSlug($data['name']);
            $data['sku'] = $data['sku'] ?? $this->skuGenerator->generate($data['category_name'] ?? null);

            if ($image) {
                $data['image_path'] = $this->storeImage($image);
            }

            unset($data['category_name']); // not a column, only used for SKU prefix hinting

            return $this->productRepository->create($data);
        });
    }

    public function update(Product $product, array $data, ?UploadedFile $image = null): Product
    {
        return DB::transaction(function () use ($product, $data, $image) {
            if (isset($data['name']) && $data['name'] !== $product->name) {
                $data['slug'] = $this->uniqueSlug($data['name'], $product->id);
            }

            if ($image) {
                $this->deleteImage($product);
                $data['image_path'] = $this->storeImage($image);
            }

            return $this->productRepository->update($product, $data);
        });
    }

    public function delete(Product $product): bool
    {
        // Soft delete only — a product referenced by historical sale_items
        // must never be hard-deleted, or receipt reprints and profit reports
        // would lose their product reference. The image file is intentionally
        // NOT deleted here for the same reason (old receipts may still
        // reference it); a separate cleanup job can prune orphaned images
        // after the retention window if storage cost becomes a concern.
        return $this->productRepository->delete($product);
    }

    public function lowStock(?int $warehouseId = null): Collection
    {
        return $this->productRepository->lowStock($warehouseId);
    }

    public function bestSelling(\DateTimeInterface $from, \DateTimeInterface $to, int $limit = 10): Collection
    {
        return $this->productRepository->bestSelling($from, $to, $limit);
    }

    public function searchForPos(string $term, int $warehouseId, int $limit = 20): Collection
    {
        return $this->productRepository->searchForPos($term, $warehouseId, $limit);
    }

    public function findByBarcode(string $barcode): ?Product
    {
        return $this->productRepository->findByBarcode($barcode);
    }

    public function skuExists(string $sku, ?int $excludeId = null): bool
    {
        return $this->productRepository->skuExists($sku, $excludeId);
    }

    public function barcodeExists(string $barcode, ?int $excludeId = null): bool
    {
        return $this->productRepository->barcodeExists($barcode, $excludeId);
    }

    private function uniqueSlug(string $name, ?int $excludeId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while ($this->productRepository->query()
            ->where('slug', $slug)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists()
        ) {
            $slug = "{$base}-" . $i++;
        }

        return $slug;
    }

    /**
     * Resizes to a max 1000px bounding box (no upscaling), strips EXIF via
     * re-encode, and stores as webp for smaller storage/bandwidth. Original
     * filename is never trusted — a random name avoids path traversal /
     * overwrite issues from user-controlled input.
     */
    private function storeImage(UploadedFile $image): string
    {
        $filename = 'products/' . Str::uuid() . '.webp';

        $encoded = Image::read($image)
            ->scaleDown(width: 1000, height: 1000)
            ->toWebp(quality: 85);

        Storage::disk('public')->put($filename, (string) $encoded);

        return $filename;
    }

    private function deleteImage(Product $product): void
    {
        if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
        }
    }
}
