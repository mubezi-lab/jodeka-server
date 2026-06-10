<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Saving;

class SavingApiController extends Controller
{
    public function index()
    {
        return response()->json(
            Saving::all()
        );
    }
}