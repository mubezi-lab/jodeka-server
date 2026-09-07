<?php

namespace App\Services;

use App\Models\DailyStockClosing;
use App\Models\InventoryReorderPolicy;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DailyStockClosingService
{
    public function close(array $data, int $userId): DailyStockClosing
    {
        return DB::transaction(function () use ($data, $userId) {
            $businessId = (int) $data['business_id'];
            $date = $data['closing_date'];

            if (DailyStockClosing::where('business_id', $businessId)->whereDate('closing_date', $date)->exists()) {
                throw ValidationException::withMessages(['closing_date' => 'Stock ya branch hii tayari imefungwa kwa tarehe hii.']);
            }

            $latest = DailyStockClosing::where('business_id', $businessId)->latest('closing_date')->first();
            if ($latest && $latest->closing_date->toDateString() > $date) {
                throw ValidationException::withMessages(['closing_date' => 'Stock za nyuma lazima ziingizwe kwa mpangilio wa tarehe.']);
            }

            $closing = DailyStockClosing::create([
                'business_id' => $businessId,
                'closing_date' => $date,
                'status' => 'submitted',
                'notes' => $data['notes'] ?? null,
                'recorded_by' => $userId,
                'submitted_at' => now(),
            ]);

            foreach ($data['items'] as $row) {
                $product = Product::findOrFail($row['product_id']);
                $previous = $this->previousItem($businessId, $product->id, $date);
                $fromDate = $previous?->closing?->closing_date?->toDateString();

                $purchaseQuery = Purchase::where('business_id', $businessId)
                    ->where('product_id', $product->id)
                    ->whereDate('date', '<=', $date);
                if ($fromDate) {
                    $purchaseQuery->whereDate('date', '>', $fromDate);
                } else {
                    // Initial opening is the stock at the start of this first day.
                    // Therefore only receipts dated on the first closing day are added.
                    $purchaseQuery->whereDate('date', $date);
                }

                $receivedUnits = (float) (clone $purchaseQuery)->sum('quantity');
                $receivedValue = (float) (clone $purchaseQuery)->get()->sum(
                    fn ($purchase) => $purchase->total_cost ?? ((float) $purchase->quantity * (float) $purchase->unit_cost)
                );
                $openingUnits = $previous
                    ? (float) $previous->closing_units
                    : (float) ($row['opening_units'] ?? 0);
                $openingValue = $previous
                    ? (float) $previous->closing_stock_value
                    : $openingUnits * (float) ($row['opening_cost_per_unit'] ?? $product->buy_price_per_unit ?? 0);
                $available = $openingUnits + $receivedUnits;
                $closingUnits = (float) $row['closing_units'];

                if ($closingUnits > $available) {
                    throw ValidationException::withMessages([
                        'items' => $product->name.': closing stock haiwezi kuzidi opening pamoja na zilizopokelewa.',
                    ]);
                }

                $sold = $available - $closingUnits;
                $averageCost = $available > 0 ? ($openingValue + $receivedValue) / $available : 0;
                $sellingPrice = (float) $row['selling_price'];
                $revenue = $sold * $sellingPrice;
                $cogs = $sold * $averageCost;

                $closing->items()->create([
                    'product_id' => $product->id,
                    'opening_units' => $openingUnits,
                    'received_units' => $receivedUnits,
                    'available_units' => $available,
                    'closing_units' => $closingUnits,
                    'sold_units' => $sold,
                    'selling_price' => $sellingPrice,
                    'average_cost_per_unit' => $averageCost,
                    'revenue' => $revenue,
                    'cost_of_goods_sold' => $cogs,
                    'gross_profit' => $revenue - $cogs,
                    'closing_stock_value' => $closingUnits * $averageCost,
                ]);
            }

            $this->createAutoRequest($closing, $userId);
            return $closing->load(['items.product', 'autoRequest.items']);
        });
    }

    private function previousItem(int $businessId, int $productId, string $date)
    {
        return \App\Models\DailyStockClosingItem::where('product_id', $productId)
            ->whereHas('closing', fn ($q) => $q->where('business_id', $businessId)->whereDate('closing_date', '<', $date))
            ->with('closing')->latest('daily_stock_closing_id')->first();
    }

    private function createAutoRequest(DailyStockClosing $closing, int $userId): ?StockRequest
    {
        $policies = InventoryReorderPolicy::with('product')
            ->where('business_id', $closing->business_id)->where('is_active', true)->get()->keyBy('product_id');
        $suggestions = [];

        foreach ($closing->items as $item) {
            $policy = $policies->get($item->product_id);
            if (!$policy || (float) $item->closing_units >= (float) $policy->minimum_units) continue;

            $incoming = $this->incomingUnits($closing->business_id, $item->product_id);
            $needed = max(0, (float) $policy->target_units - ((float) $item->closing_units + $incoming));
            $unitsPerPackage = max(1, (float) $policy->product->units_per_package);
            $packages = (int) ceil($needed / $unitsPerPackage);
            if ($packages > 0) $suggestions[] = [$item->product_id, $packages, $unitsPerPackage];
        }

        if (!$suggestions) return null;

        $request = StockRequest::create([
            'request_number' => 'AUTO-'.now()->format('Ymd').'-'.strtoupper(Str::random(8)),
            'business_id' => $closing->business_id,
            'request_date' => $closing->closing_date,
            'status' => 'draft',
            'source' => 'auto',
            'daily_stock_closing_id' => $closing->id,
            'notes' => 'Generated automatically from daily stock closing.',
            'requested_by' => $userId,
        ]);
        foreach ($suggestions as [$productId, $packages, $unitsPerPackage]) {
            $request->items()->create([
                'product_id' => $productId,
                'requested_packages' => $packages,
                'units_per_package' => $unitsPerPackage,
                'notes' => 'Auto reorder suggestion',
            ]);
        }
        return $request;
    }

    private function incomingUnits(int $businessId, int $productId): float
    {
        $units = 0;
        $requests = StockRequest::with(['items', 'purchaseOrder.items.receiptItems'])
            ->where('business_id', $businessId)
            ->whereIn('status', ['draft','pending','approved','ordered','partially_received'])
            ->get();

        foreach ($requests as $request) {
            $item = $request->items->firstWhere('product_id', $productId);
            if (!$item) continue;
            if (in_array($request->status, ['draft','pending'], true)) {
                $units += (float) $item->requested_packages * (float) $item->units_per_package;
            } elseif ($request->status === 'approved') {
                $units += (float) $item->approved_packages * (float) $item->units_per_package;
            } else {
                $orderItem = $request->purchaseOrder?->items->firstWhere('product_id', $productId);
                if ($orderItem) {
                    $remainingPackages = max(0, (float) $orderItem->ordered_packages - (float) $orderItem->receiptItems->sum('received_packages'));
                    $units += $remainingPackages * (float) $orderItem->units_per_package;
                }
            }
        }
        return $units;
    }
}
