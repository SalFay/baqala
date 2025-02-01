<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;

class PointOfSale extends Controller
{
    public function index()
    {
        $products = ProductResource::collection(Product::all());
        return view('admin.pos.index', compact('products'));
    }

}
