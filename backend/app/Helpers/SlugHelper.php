<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SlugHelper
{
    /**
     * Generate a unique slug for the given model/column, ignoring the given id on updates.
     */
    public static function unique(string $class, string $value, string $column = 'slug', ?int $ignoreId = null): string
    {
        $slug = Str::slug($value);
        $original = $slug;
        $suffix = 1;

        /** @var Model $model */
        $model = new $class;

        while (
            $model->newQuery()
                ->where($column, $slug)
                ->when($ignoreId, fn ($query) => $query->where($model->getKeyName(), '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$original}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
