<?php

namespace App\Models;

use Database\Factories\AdPlacementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdPlacement extends Model
{
    /** @use HasFactory<AdPlacementFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'page_type',
        'position',
        'code',
        'is_enabled',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }
}
