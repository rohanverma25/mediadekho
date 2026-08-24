<?php

namespace App\Repositories\Eloquent;

use App\Models\MediaCategory;
use App\Repositories\Contracts\MediaCategoryRepositoryInterface;
use Illuminate\Support\Collection;

class MediaCategoryRepository implements MediaCategoryRepositoryInterface
{
    public function tree(): Collection
    {
        return MediaCategory::query()
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get();
    }

    public function find(int $id): MediaCategory
    {
        return MediaCategory::query()->findOrFail($id);
    }

    public function create(array $data): MediaCategory
    {
        return MediaCategory::query()->create($data);
    }

    public function update(MediaCategory $category, array $data): MediaCategory
    {
        $category->update($data);

        return $category->refresh();
    }

    public function delete(MediaCategory $category): bool
    {
        return (bool) $category->delete();
    }
}
