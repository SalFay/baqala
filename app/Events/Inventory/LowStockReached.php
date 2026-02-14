<?php

namespace App\Events\Inventory;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LowStockReached
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Product $product,
        public Store $store,
        public int $currentQuantity,
        public int $threshold
    ) {}
}
