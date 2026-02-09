<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'name_ar', 'min_points', 'points_multiplier', 'discount_percentage',
        'benefits', 'badge_color', 'badge_icon', 'is_active', 'sort_order'
    ];

    protected $casts = [
        'min_points' => 'integer',
        'points_multiplier' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'benefits' => 'array',
        'is_active' => 'boolean',
    ];
}
