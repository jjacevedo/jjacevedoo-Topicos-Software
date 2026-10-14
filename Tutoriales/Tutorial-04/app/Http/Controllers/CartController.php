<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $products = [
            [
                'id' => 121,
                'name' => 'Tv samsung',
                'price' => 1000,
            ],
            [
                'id' => 11,
                'name' => 'Iphone',
                'price' => 2000,
            ],
        ];

        $cartProductData = $request->session()->get('cart_product_data');

        return view('cart.index')
            ->with('products', $products)
            ->with('cartProductData', $cartProductData);
    }

    public function add(Request $request, $id)
    {
        $products = [
            [
                'id' => 121,
                'name' => 'Tv samsung',
                'price' => 1000,
            ],
            [
                'id' => 11,
                'name' => 'Iphone',
                'price' => 2000,
            ],
        ];

        $cartProductData = $request->session()->get('cart_product_data', []);

        foreach ($products as $product) {
            if ($product['id'] == $id) {
                $cartProductData[] = $product;
                break;
            }
        }

        $request->session()->put('cart_product_data', $cartProductData);

        return redirect()->route('cart.index');
    }

    public function removeAll(Request $request)
    {
        $request->session()->forget('cart_product_data');

        return redirect()->route('cart.index');
    }
}