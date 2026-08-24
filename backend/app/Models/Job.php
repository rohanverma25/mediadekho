<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Job extends Model
{
    use HasFactory;

    /**
     * Laravel's database queue driver already owns a `jobs` table — this
     * career-listing model lives in `jobs_board` to avoid colliding with it.
     */
    protected $table = 'jobs_board';

    protected $fillable = [
        'title',
        'description',
        'department',
        'location',
        'type',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }
}
