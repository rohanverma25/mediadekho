<?php

namespace App\Repositories\Contracts;

use App\Models\MediaCategory;
use Illuminate\Support\Collection;

interface MediaCategoryRepositoryInterface
{
    /**
     * Top-level categories with their children eager loaded, for tree-style listings.
     */
    public function tree(): Collection;

    public function find(int $id): MediaCategory;

    public function create(array $data): MediaCategory;

    public function update(MediaCategory $category, array $data): MediaCategory;

    public function delete(MediaCategory $category): bool;
}
