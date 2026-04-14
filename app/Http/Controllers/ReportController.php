<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Stock;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function monthly()
    {
        $from = request('from') ?? now()->startOfMonth();
        $to = request('to') ?? now();

        $products = Product::all();

        $report = [];

        $totalSales = 0;
        $totalPurchase = 0;
        $totalCostOfSold = 0;

        foreach ($products as $product) {

            // ✅ Opening Stock (last closing before period)
            $opening = Stock::where('product_id', $product->id)
                ->whereDate('date', '<', $from)
                ->orderBy('date', 'desc')
                ->value('closing_stock') ?? 0;

            // ✅ Purchases ndani ya period
            $purchased = Purchase::where('product_id', $product->id)
                ->whereBetween('date', [$from, $to])
                ->sum('quantity') ?? 0;

            // ✅ Sold ndani ya period
            $sold = Stock::where('product_id', $product->id)
                ->whereBetween('date', [$from, $to])
                ->sum('sold') ?? 0;

            // ✅ Total available
            $totalAvailable = $opening + $purchased;

            // ✅ Closing (calculated, not from DB)
            $closing = $totalAvailable - $sold;

            // ✅ Prices
            $sellPrice = $product->sell_price ?? 0;
            $buyPrice = $product->buy_price ?? 0;

            // ✅ Sales
            $sales = $sold * $sellPrice;

            // ✅ COST OF SOLD (VERY IMPORTANT FIX)
            $costOfSold = 0;
            if ($totalAvailable > 0) {
                $costOfSold = ($sold / $totalAvailable) * ($purchased * $buyPrice);
            }

            // ✅ Profit
            $profit = $sales - $costOfSold;

            // ✅ Totals
            $totalSales += $sales;
            $totalPurchase += ($purchased * $buyPrice);
            $totalCostOfSold += $costOfSold;

            $report[] = [
                'name' => $product->name,
                'opening' => $opening,
                'purchase' => $purchased,
                'total' => $totalAvailable,
                'closing' => $closing,
                'sold' => $sold,
                'price' => $sellPrice,
                'total_price' => $sales,
                'cost' => $costOfSold,
                'profit' => $profit,
            ];
        }

    $data = [
        'report' => $report,
        'totalSales' => $totalSales,
        'totalPurchase' => $totalPurchase,
        'cash' => $totalSales,

        // 🔥 mpya
        'balance_due' => $totalSales,
        'last_capital' => 0, // unaweza calculate later
        'current_capital' => $totalSales - $totalCostOfSold,
        'out_source' => 0,
        'total_expenditure' => 0,
        'seller' => 'Jackson',
        'business' => 'Jodeka',

        'loss' => $totalPurchase - $totalSales,
        'from' => $from,
        'to' => $to
    ];

        $pdf = Pdf::loadView('reports.monthly_pdf', $data);

        return $pdf->download('monthly-report.pdf');
    }
}