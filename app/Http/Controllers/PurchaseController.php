<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Business;

class PurchaseController extends Controller
{
    /**
     * Display all purchases
     */
    public function index()
    {
        $purchases = Purchase::with(['product', 'business'])
            ->latest()
            ->get();

        return view('purchases.index', compact('purchases'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $products = Product::all();
        $businesses = Business::all();

        return view('purchases.create', compact('products', 'businesses'));
    }

    /**
     * Store purchase (✅ FIXED)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id'   => ['required', 'exists:products,id'],
            'business_id'  => ['required', 'exists:businesses,id'],
            'quantity'     => ['required', 'numeric', 'min:1'], // packages
            'unit_cost'    => ['required', 'numeric', 'min:0'], // cost per package
            'date'         => ['required', 'date'],
            'supplier'     => ['nullable', 'string', 'max:255'],
            'notes'        => ['nullable', 'string'],
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // ✅ NEW FIELD
        $unitsPerPackage = $product->units_per_package;

        // 🚨 PROTECTION (NO MORE DIVISION ERROR)
        if (!$unitsPerPackage || $unitsPerPackage <= 0) {
            return back()->with('error', 'Hii product haina units_per_package sahihi');
        }

        $packages = $validated['quantity'];

        // 🔥 conversions
        $totalUnits = $packages * $unitsPerPackage;
        $costPerUnit = $validated['unit_cost'] / $unitsPerPackage;
        $totalCost = $validated['unit_cost'] * $packages;

        Purchase::create([
            'product_id'  => $validated['product_id'],
            'business_id' => $validated['business_id'],
            'quantity'    => $totalUnits, // units
            'unit_cost'   => $costPerUnit,
            'total_cost'  => $totalCost,
            'date'        => $validated['date'],
            'supplier'    => $validated['supplier'],
            'notes'       => $validated['notes'],
        ]);

        return redirect()
            ->route('purchases.index')
            ->with('success', 'Purchase recorded successfully');
    }

    /**
     * Show edit form
     */
    public function edit(Purchase $purchase)
    {
        $products = Product::all();
        $businesses = Business::all();

        return view('purchases.edit', compact('purchase', 'products', 'businesses'));
    }

    /**
     * Update purchase (✅ FIXED)
     */
    public function update(Request $request, Purchase $purchase)
    {
        $validated = $request->validate([
            'product_id'   => ['required', 'exists:products,id'],
            'business_id'  => ['required', 'exists:businesses,id'],
            'quantity'     => ['required', 'numeric', 'min:1'],
            'unit_cost'    => ['required', 'numeric', 'min:0'],
            'date'         => ['required', 'date'],
            'supplier'     => ['nullable', 'string', 'max:255'],
            'notes'        => ['nullable', 'string'],
        ]);

        $product = Product::findOrFail($validated['product_id']);

        $unitsPerPackage = $product->units_per_package;

        // 🚨 guard
        if (!$unitsPerPackage || $unitsPerPackage <= 0) {
            return back()->with('error', 'Hii product haina units_per_package sahihi');
        }

        $packages = $validated['quantity'];

        $totalUnits = $packages * $unitsPerPackage;
        $costPerUnit = $validated['unit_cost'] / $unitsPerPackage;
        $totalCost = $validated['unit_cost'] * $packages;

        $purchase->update([
            'product_id'  => $validated['product_id'],
            'business_id' => $validated['business_id'],
            'quantity'    => $totalUnits,
            'unit_cost'   => $costPerUnit,
            'total_cost'  => $totalCost,
            'date'        => $validated['date'],
            'supplier'    => $validated['supplier'],
            'notes'       => $validated['notes'],
        ]);

        return redirect()
            ->route('purchases.index')
            ->with('success', 'Purchase updated successfully');
    }

    /**
     * Delete purchase
     */
    public function destroy(Purchase $purchase)
    {
        $purchase->delete();

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase deleted successfully');
    }
}