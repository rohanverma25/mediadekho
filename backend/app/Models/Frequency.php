<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Frequency extends Model
{
    /** @use HasFactory<\Database\Factories\FrequencyFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
    ];
}
