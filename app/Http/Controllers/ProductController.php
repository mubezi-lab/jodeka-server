<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index()
    {
        // Sort A-Z bila kujali uppercase/lowercase
        $products = Product::orderByRaw('LOWER(name) ASC')->get();

        return view('products.index', compact('products'));
    }

    /**
     * Show form to create product
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store new product
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'package_type' => 'required|string|max:100',
            'units_per_package' => 'required|numeric|min:1',

            // USER INPUT
            'buy_price_per_package' => 'required|numeric|min:0',
            'sell_price_per_unit' => 'required|numeric|min:0',
        ]);

        $units = $validated['units_per_package'];

        // 🔥 CALCULATIONS (CORE LOGIC)
        $validated['buy_price_per_unit'] = $validated['buy_price_per_package'] / $units;
        $validated['sell_price_per_package'] = $validated['sell_price_per_unit'] * $units;

        // Clean name format
        $validated['name'] = ucfirst(strtolower($validated['name']));

        Product::create($validated);

        return redirect()->route('products.index')
                         ->with('success', 'Product created successfully');
    }

    /**
     * Show form to edit product
     */
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    /**
     * Update product
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'package_type' => 'required|string|max:100',
            'units_per_package' => 'required|numeric|min:1',

            // USER INPUT
            'buy_price_per_package' => 'required|numeric|min:0',
            'sell_price_per_unit' => 'required|numeric|min:0',
        ]);

        $units = $validated['units_per_package'];

        // 🔥 CALCULATIONS
        $validated['buy_price_per_unit'] = $validated['buy_price_per_package'] / $units;
        $validated['sell_price_per_package'] = $validated['sell_price_per_unit'] * $units;

        // Clean name format
        $validated['name'] = ucfirst(strtolower($validated['name']));

        $product->update($validated);

        return redirect()->route('products.index')
                         ->with('success', 'Product updated successfully');
    }

    /**
     * Delete product
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
                         ->with('success', 'Product deleted successfully');
    }
}