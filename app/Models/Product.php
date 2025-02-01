<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'arabic_name', 'pid', 'purchase_price', 'sale_price', 'category_id', 'status', 'taxable', 'taxable_price', 'product_image'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $with = ['category'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class)->where('status', 'Available');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }

    public function getImageAttribute()
    {
        return $this->product_image ? asset('storage/' . $this->product_image) : asset('assets/no-prod-image.jpg');
    }

    public function getFullNameAttribute()
    {
        return "{$this->name} {$this->category->code}";
    }

}
