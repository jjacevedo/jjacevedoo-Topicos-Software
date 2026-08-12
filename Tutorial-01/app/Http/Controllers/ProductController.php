<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $viewData = [];
        $viewData["title"] = "Products - Online Store";
        $viewData["subtitle"] = "List of products";

        $viewData["products"] = [
            [
                "id" => 1,
                "name" => "TV",
                "price" => 500
            ],
            [
                "id" => 2,
                "name" => "iPhone",
                "price" => 1000
            ],
            [
                "id" => 3,
                "name" => "Chromecast",
                "price" => 100
            ],
            [
                "id" => 4,
                "name" => "Glasses",
                "price" => 50
            ]
        ];

        return view('product.index')->with("viewData", $viewData);
    }

    public function show(string $id): View
    {
        $viewData = [];
        $viewData["title"] = "Product - Online Store";
        $viewData["subtitle"] = "Product detail";

        $viewData["product"] = [
            "id" => $id,
            "name" => "TV",
            "price" => 500,
            "description" => "This is a description of the product",
            "image" => "product.jpg"
        ];

        return view('product.show')->with("viewData", $viewData);
    }
}