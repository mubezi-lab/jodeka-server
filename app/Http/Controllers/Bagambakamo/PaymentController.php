<?php

namespace App\Http\Controllers\Bagambakamo;

use App\Http\Controllers\Controller;
use App\Models\Bagambakamo\Payment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'member_id' => [
                'required',
                Rule::exists(
                    'bagambakamo_members',
                    'id'
                ),
            ],

            'amount' => [
                'required',
                'numeric',
                'min:0',
            ],

            'type' => [
                'required',
                Rule::in([
                    'monthly',
                    'mchango',
                ]),
            ],

            'description' => [
                'nullable',
                'string',
                'max:255',
            ],

            'payment_date' => [
                'required',
                'date',
            ],

            'method' => [
                'nullable',
                'string',
                'max:255',
            ],

            'reference' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        Payment::create([
            'member_id' => $request->member_id,
            'amount' => $request->amount,
            'type' => $request->type,
            'description' => $request->description,
            'payment_date' => $request->payment_date,
            'method' => $request->method,
            'reference' => $request->reference,
        ]);

        return back()->with(
            'success',
            'Payment added successfully'
        );
    }
}