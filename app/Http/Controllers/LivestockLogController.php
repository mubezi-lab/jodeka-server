<?php

namespace App\Http\Controllers;

use App\Models\Livestock;
use App\Models\LivestockLog;
use Illuminate\Http\Request;

class LivestockLogController extends Controller
{
    public function create(Request $request)
    {
        $livestocks = Livestock::all();

        return view('livestock-logs.create', [
            'livestocks' => $livestocks,
            'selectedLivestock' => $request->livestock_id,
            'type' => $request->type,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'livestock_id' => 'required|exists:livestocks,id',
            'type'         => 'required|in:expense,mortality',
            'category'     => 'nullable|string',
            'quantity'     => 'nullable|integer|min:1',
            'amount'       => 'nullable|numeric|min:0',
            'date'         => 'required|date',
            'note'         => 'nullable|string',
        ]);

        $livestock = Livestock::findOrFail($data['livestock_id']);

        /*
        |--------------------------------------------------------------------------
        | 🔴 FIX KUU (MUHIMU SANA)
        |--------------------------------------------------------------------------
        */

        if ($data['type'] === 'mortality') {

            // mortality haina category wala amount
            $data['category'] = null;
            $data['amount'] = null;

            if ($data['quantity'] > $livestock->quantity) {
                return back()->withErrors([
                    'quantity' => 'Cannot exceed available stock'
                ]);
            }

            // punguza stock
            $livestock->decrement('quantity', $data['quantity']);
        }

        if ($data['type'] === 'expense') {

            // 🔥 HAPA NDIO FIX YA CATEGORY
            if (empty($data['category'])) {
                return back()->withErrors([
                    'category' => 'Category is required for expense'
                ]);
            }

            // kuhakikisha quantity ipo
            $data['quantity'] = $data['quantity'] ?? 0;
        }

        /*
        |--------------------------------------------------------------------------
        | SAVE DATA
        |--------------------------------------------------------------------------
        */

        LivestockLog::create([
            'livestock_id' => $data['livestock_id'],
            'type'         => $data['type'],
            'category'     => $data['category'], // 🔥 sasa itaingia
            'quantity'     => $data['quantity'],
            'amount'       => $data['amount'],
            'date'         => $data['date'],
            'note'         => $data['note'],
        ]);

        return redirect()
            ->route('livestocks.show', $livestock->id)
            ->with('success', 'Record added successfully');
    }
}