<?php

namespace App\Repositories\Eloquent;

use App\Models\MediaInventory;
use App\Repositories\Contracts\MediaInventoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class MediaInventoryRepository implements MediaInventoryRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->applyFilters(MediaInventory::query(), $filters)
            ->with(['category', 'subcategory', 'frequency', 'language', 'price', 'creator'])
            ->latest()
            ->paginate($perPage);
    }

    public function find(int $id): MediaInventory
    {
        return MediaInventory::query()->findOrFail($id);
    }

    public function findBySlug(string $slug): MediaInventory
    {
        return MediaInventory::query()->where('slug', $slug)->firstOrFail();
    }

    public function create(array $data): MediaInventory
    {
        return MediaInventory::query()->create($data);
    }

    public function update(MediaInventory $inventory, array $data): MediaInventory
    {
        $inventory->update($data);

        return $inventory->refresh();
    }

    public function delete(MediaInventory $inventory): bool
    {
        return (bool) $inventory->delete();
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['category_id'] ?? null, fn (Builder $q, $value) => $q->where('category_id', $value))
            ->when($filters['subcategory_id'] ?? null, fn (Builder $q, $value) => $q->where('subcategory_id', $value))
            ->when($filters['frequency_id'] ?? null, fn (Builder $q, $value) => $q->where('frequency_id', $value))
            ->when($filters['language_id'] ?? null, fn (Builder $q, $value) => $q->where('language_id', $value))
            ->when($filters['status'] ?? null, fn (Builder $q, $value) => $q->where('status', $value))
            ->when($filters['show_on_deals'] ?? null, fn (Builder $q) => $q->where('show_on_deals', true))
            ->when($filters['date_from'] ?? null, fn (Builder $q, $value) => $q->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $q, $value) => $q->whereDate('created_at', '<=', $value))
            ->when($filters['search'] ?? null, function (Builder $q, $value) {
                $q->where(function (Builder $q) use ($value) {
                    $q->where('title', 'like', "%{$value}%")
                        ->orWhere('short_description', 'like', "%{$value}%");
                });
            });
    }
}
