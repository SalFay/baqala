<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnReason extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'name_ar', 'is_active', 'requires_notes', 'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_notes' => 'boolean',
    ];
}
