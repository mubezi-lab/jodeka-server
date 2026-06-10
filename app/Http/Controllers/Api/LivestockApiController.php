<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Livestock;

class LivestockApiController extends Controller
{
    public function index()
    {
        return response()->json(
            Livestock::all()
        );
    }
}