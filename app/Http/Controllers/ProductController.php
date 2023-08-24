<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public $STORAGE_LOCATION = './products.json';
    public $rules = [
        'name' => 'required|string|min:2',
        'quantity' => 'required|numeric',
        'price' => 'required|numeric'
    ];

    /**
     * Get data from the file.
     */
    public function index()
    {
        // TODO: Use local file driver instead
        $data = file_get_contents($this->STORAGE_LOCATION);

        $products_arr = json_decode($data, true);

        return $products_arr;
    }

    /**
     * Store a new product.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules);

        if ($validator->fails()) return $validator->errors()->first();

        $data = $this->index();

        $request->merge([
            'id' => count($data) + 1,
            'created_at' => now()
        ]);

        $data[] = array($request->except('_token'));

        return $this->updateProduct($data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules);

        if ($validator->fails()) return [
            'status' => false,
            'message' => $validator->errors()->first()
        ];

        $data = $this->index();

        $productIndex = array_search($request->id, array_column($data, 'id'));
        if (!$productIndex) return "Product not found.";

        // TODO: Consider using array_map (if performance is better for large data)
        foreach ($data as $key => $product) {
            if ($product['id'] == $request->id) {
                $data[$key]['name'] = $request->name;
                $data[$key]['quantity'] = $request->quantity;
                $data[$key]['price'] = $request->price;
            }
        }

        return $this->updateProduct($data);
    }

    /**
     * Update products
     */
    public function updateProduct($newData)
    {
        //
        file_put_contents($this->STORAGE_LOCATION, json_encode($newData));

        return [
            'status' => true,
            'message' => 'success'
        ];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = $this->index();

        $productIndex = array_search($id, array_column($data, 'id'));
        if (!$productIndex) return "Product not found.";

        unset($data[$id]);

        return $this->updateProduct($data);
    }
}
