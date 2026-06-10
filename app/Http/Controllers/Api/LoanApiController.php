<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loan;

class LoanApiController extends Controller
{
    public function index()
    {
        return response()->json(
            Loan::all()
        );
    }
}