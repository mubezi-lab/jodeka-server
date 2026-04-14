<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stock;
use App\Models\Product;
use App\Models\Business;
use App\Models\Purchase;
use Carbon\Carbon;

class StockController extends Controller
{
    public function index()
    {
        $stocks = Stock::with(['product', 'business'])
            ->latest()
            ->get();

        return view('stocks.index', compact('stocks'));
    }

    public function create()
    {
        return view('stocks.create', [
            'products' => Product::all(),
            'businesses' => Business::all()
        ]);
    }

    public function edit(Stock $stock)
    {
        return view('stocks.edit', [
            'stock' => $stock,
            'products' => Product::all(),
            'businesses' => Business::all()
        ]);
    }

    public function show(Stock $stock)
    {
        return view('stocks.show', compact('stock'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id'     => 'required|exists:products,id',
            'business_id'    => 'required|exists:businesses,id',
            'opening_stock'  => 'required|integer|min:0',
            'purchased'      => 'required|numeric|min:0',
            'closing_stock'  => 'required|integer|min:0',
            'date'           => 'required|date',
            'price'          => 'required|numeric|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);
        $sellingPrice = $request->price;

        $product->update(['price' => $sellingPrice]);

        $total = $request->opening_stock + $request->purchased;
        $sold  = $total - $request->closing_stock;

        if ($sold < 0) {
            return back()->withErrors([
                'closing_stock' => 'Closing stock cannot be greater than total stock.'
            ])->withInput();
        }

        // SALES
        $sales = $sold * $sellingPrice;

        // 🔥 GET ALL PURCHASES (NOT LAST ONLY)
        $purchases = Purchase::where('product_id', $request->product_id)
            ->where('business_id', $request->business_id)
            ->whereDate('date', '<=', $request->date)
            ->get();

        $totalQty = $purchases->sum('quantity');

        $totalCost = $purchases->sum(function ($p) {
            return $p->quantity * $p->unit_cost;
        });

        // 🔥 EXACT COST (NO ROUNDING)
        $cost = $totalQty > 0 
            ? ($sold / $totalQty) * $totalCost 
            : 0;

        // 🔥 EXACT PROFIT
        $profit = $sales - $cost;

        Stock::create([
            'product_id'    => $request->product_id,
            'business_id'   => $request->business_id,
            'opening_stock' => $request->opening_stock,
            'purchased'     => $request->purchased,
            'total_stock'   => $total,
            'closing_stock' => $request->closing_stock,
            'sold'          => $sold,
            'price'         => $sellingPrice,
            'sales_amount'  => $sales,
            'profit'        => $profit,
            'date'          => $request->date,
        ]);

        return redirect()->route('stocks.index')
            ->with('success', 'Stock recorded successfully');
    }

    public function update(Request $request, Stock $stock)
    {
        $request->validate([
            'product_id'     => 'required|exists:products,id',
            'business_id'    => 'required|exists:businesses,id',
            'opening_stock'  => 'required|integer|min:0',
            'purchased'      => 'required|numeric|min:0',
            'closing_stock'  => 'required|integer|min:0',
            'date'           => 'required|date',
            'price'          => 'required|numeric|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);
        $sellingPrice = $request->price;

        $product->update(['price' => $sellingPrice]);

        $total = $request->opening_stock + $request->purchased;
        $sold  = $total - $request->closing_stock;

        if ($sold < 0) {
            return back()->withErrors([
                'closing_stock' => 'Closing stock cannot be greater than total stock.'
            ])->withInput();
        }

        $sales = $sold * $sellingPrice;

        // 🔥 GET ALL PURCHASES
        $purchases = Purchase::where('product_id', $request->product_id)
            ->where('business_id', $request->business_id)
            ->whereDate('date', '<=', $request->date)
            ->get();

        $totalQty = $purchases->sum('quantity');

        $totalCost = $purchases->sum(function ($p) {
            return $p->quantity * $p->unit_cost;
        });

        $cost = $totalQty > 0 
            ? ($sold / $totalQty) * $totalCost 
            : 0;

        $profit = $sales - $cost;

        $stock->update([
            'product_id'    => $request->product_id,
            'business_id'   => $request->business_id,
            'opening_stock' => $request->opening_stock,
            'purchased'     => $request->purchased,
            'total_stock'   => $total,
            'closing_stock' => $request->closing_stock,
            'sold'          => $sold,
            'price'         => $sellingPrice,
            'sales_amount'  => $sales,
            'profit'        => $profit,
            'date'          => $request->date,
        ]);

        return redirect()->route('stocks.index')
            ->with('success', 'Stock updated successfully');
    }

    public function destroy(Stock $stock)
    {
        $stock->delete();

        return back()->with('success', 'Stock deleted successfully');
    }

    public function getStockData(Request $request)
    {
        $request->validate([
            'product_id'  => 'required|exists:products,id',
            'business_id' => 'required|exists:businesses,id',
            'date'        => 'required|date',
        ]);

        $productId  = $request->product_id;
        $businessId = $request->business_id;
        $date       = Carbon::parse($request->date);

        $lastStock = Stock::where('product_id', $productId)
            ->where('business_id', $businessId)
            ->orderByDesc('date')
            ->first();

        $openingStock = $lastStock?->closing_stock ?? 0;

        if ($lastStock) {
            $lastDate = Carbon::parse($lastStock->date);

            $purchased = Purchase::where('product_id', $productId)
                ->where('business_id', $businessId)
                ->whereDate('date', '>', $lastDate)
                ->whereDate('date', '<=', $date)
                ->sum('quantity');
        } else {
            $purchased = Purchase::where('product_id', $productId)
                ->where('business_id', $businessId)
                ->whereDate('date', '<=', $date)
                ->sum('quantity');
        }

        $price = Product::find($productId)?->price ?? 0;

        return response()->json([
            'opening_stock' => $openingStock,
            'purchased'     => $purchased,
            'price'         => $price,
        ]);
    }
}