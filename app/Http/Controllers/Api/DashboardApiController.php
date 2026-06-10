<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Livestock;
use App\Models\Loan;
use App\Models\Saving;

class DashboardApiController extends Controller
{
    public function index()
    {
        return response()->json([
            'products'   => Product::count(),
            'stocks'     => Stock::count(),
            'livestocks' => Livestock::count(),
            'loans'      => Loan::count(),
            'savings'    => Saving::count(),
        ]);
    }
}