<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductApiController extends Controller
{
    public function index()
    {
        return response()->json(
            Product::all()
        );
    }
}