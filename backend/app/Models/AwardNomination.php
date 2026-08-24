<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AwardNomination extends Model
{
    use HasFactory;

    protected $fillable = [
        'award_id',
        'user_id',
        'name',
        'email',
        'phone',
        'company_name',
        'description',
        'status',
    ];

    public function award(): BelongsTo
    {
        return $this->belongsTo(Award::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
