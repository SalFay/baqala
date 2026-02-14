<?php

namespace App\Events\Inventory;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockAdjusted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Product $product,
        public Store $store,
        public int $quantity,
        public int $previousQuantity,
        public string $type,
        public ?string $reason = null
    ) {}
}
