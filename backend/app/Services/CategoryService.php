<?php

namespace App\Services;

use App\Helpers\ImageUploadHelper;
use App\Helpers\SlugHelper;
use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

class CategoryService
{
    private const IMAGE_DIRECTORY = 'categories';

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Category::query()->latest()->paginate($perPage);
    }

    public function find(int $id): Category
    {
        return Category::query()->findOrFail($id);
    }

    public function create(array $data, ?UploadedFile $image = null, ?UploadedFile $mainImage = null): Category
    {
        $data['slug'] = SlugHelper::unique(Category::class, $data['name']);

        if ($image) {
            $data['image'] = ImageUploadHelper::upload($image, self::IMAGE_DIRECTORY);
        }

        if ($mainImage) {
            $data['main_image'] = ImageUploadHelper::upload($mainImage, self::IMAGE_DIRECTORY);
        }

        return Category::query()->create($data);
    }

    public function update(Category $category, array $data, ?UploadedFile $image = null, ?UploadedFile $mainImage = null): Category
    {
        if (isset($data['name']) && $data['name'] !== $category->name) {
            $data['slug'] = SlugHelper::unique(Category::class, $data['name'], 'slug', $category->id);
        }

        if ($image) {
            ImageUploadHelper::delete($category->image);
            $data['image'] = ImageUploadHelper::upload($image, self::IMAGE_DIRECTORY);
        }

        if ($mainImage) {
            ImageUploadHelper::delete($category->main_image);
            $data['main_image'] = ImageUploadHelper::upload($mainImage, self::IMAGE_DIRECTORY);
        }

        $category->update($data);

        return $category->refresh();
    }

    public function delete(Category $category): bool
    {
        ImageUploadHelper::delete($category->image);
        ImageUploadHelper::delete($category->main_image);

        return (bool) $category->delete();
    }
}
