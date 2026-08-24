<?php

namespace App\Repositories\Contracts;

use App\Models\MediaInventory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface MediaInventoryRepositoryInterface
{
    /**
     * Paginated, filtered listing for the admin DataTable and search APIs.
     *
     * Supported filter keys: category_id, subcategory_id, frequency_id,
     * language_id, status, date_from, date_to (against created_at), search
     * (matches title/short_description).
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): MediaInventory;

    public function findBySlug(string $slug): MediaInventory;

    public function create(array $data): MediaInventory;

    public function update(MediaInventory $inventory, array $data): MediaInventory;

    public function delete(MediaInventory $inventory): bool;
}
