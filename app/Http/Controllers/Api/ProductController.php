<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:191|unique:products',
            'pid' => 'required|string|max:191|unique:products',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->except('product_image');

        if ($request->hasFile('product_image')) {
            $data['product_image'] = $request->file('product_image')->store('products', 'public');
        }

        if ($request->taxable) {
            $data['taxable'] = 'Yes';
            $data['sale_price'] = round($request->sale_price / 1.15, 2);
            $data['taxable_price'] = $request->sale_price;
        } else {
            $data['taxable'] = 'No';
            $data['sale_price'] = $request->sale_price;
            $data['taxable_price'] = $request->sale_price;
        }

        $data['category_id'] = addCategory($request->category_id);

        Product::create($data);

        return response()->json(['status' => 'ok', 'message' => 'Product Added'], 200);
    }

    /**
     * @param Request $request
     * @param Product $product
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, Product $product)
    {
        $this->validate($request, [
            'name' => [
                'required', 'string', 'max:191',
                Rule::unique('products')->ignore($product->id),
            ],
            'pid' => [
                'required', 'string', 'max:191',
                Rule::unique('products')->ignore($product->id),
            ],
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->except('product_image');

        if ($request->hasFile('product_image')) {
            // Delete old image if exists
            if ($product->product_image) {
                Storage::disk('public')->delete($product->product_image);
            }

            // Store new image
            $data['product_image'] = $request->file('product_image')->store('products', 'public');
        }

        if ($request->taxable) {
            $data['taxable'] = 'Yes';
            $data['sale_price'] = round($request->sale_price / 1.15, 2);
            $data['taxable_price'] = $request->sale_price;
        } else {
            $data['taxable'] = 'No';
            $data['sale_price'] = $request->sale_price;
            $data['taxable_price'] = $request->sale_price;
        }

        $product->update($data);

        return response()->json(['status' => 'ok', 'message' => 'Product Updated'], 200);
    }

    /**
     * @param Product $product
     * @return array|string[]
     * @throws \Exception
     */
    public function destroy(Product $product): array
    {
        $product->delete();
        return ['status' => 'ok', 'message' => 'Product Deleted'];
    }
}
