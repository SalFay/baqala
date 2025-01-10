<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            'purchase_price' => 'required|between:0,99.99',
            'sale_price' => 'required|between:0,99.99'
        ],
            [
                'name.required' => 'Product Name is Required',
                'pid.required' => 'Product Barcode is Required',
                'purchase_price.required' => 'Purchase Price is Required',
                'sale_price.required' => 'Sale Price is Required'
            ]);

        $data = $request->all();
        if ($request->taxable) {
            $data['taxable'] = 'Yes';
            $price = $request->sale_price / 1.15;
            $data['sale_price'] = round($price, 2);
            $data['taxable_price'] = $request->sale_price;
        } else {
            $data['taxable'] = 'No';
            $data['taxable_price'] = $request->sale_price;
            $data['sale_price'] = $request->sale_price;
        }
        $data['category_id'] = addCategory($request->category_id);

        Product::create($data);
        return response()->json(['status' => 'ok', 'message' => 'Product Added'], 200);
    } // store

    /**
     * @param Request $request
     * @param Product $product
     * @return object
     * @throws ValidationException
     */
    public function update(Request $request, Product $product): object
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
        ]);
        //update the Product
        $data = $request->all();
        if ($request->taxable) {
            $data['taxable'] = 'Yes';
            $price = $request->sale_price / 1.15;
            $data['sale_price'] = round($price, 2);
            $data['taxable_price'] = $request->sale_price;
        } else {
            $data['taxable'] = 'No';
            $data['taxable_price'] = $request->sale_price;
            $data['sale_price'] = $request->sale_price;
        }
        $product->update($data);

        return response()->json(['status' => 'ok', 'message' => 'Product Updated'], 200);
    } // update

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
