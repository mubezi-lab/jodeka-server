<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\Purchase;
use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\StockRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProcurementService
{
    public function createRequest(array $data, int $userId): StockRequest
    {
        return DB::transaction(function () use ($data, $userId) {
            $request = StockRequest::create([
                'request_number' => $this->number('REQ'),
                'business_id' => $data['business_id'],
                'request_date' => $data['request_date'],
                'notes' => $data['notes'] ?? null,
                'requested_by' => $userId,
            ]);
            foreach ($data['items'] as $row) {
                $product = Product::findOrFail($row['product_id']);
                $request->items()->create([
                    'product_id' => $row['product_id'],
                    'requested_packages' => $row['quantity'],
                    'units_per_package' => $product->units_per_package,
                    'notes' => $row['notes'] ?? null,
                ]);
            }
            return $request;
        });
    }

    public function review(StockRequest $request, array $data, int $userId): StockRequest
    {
        if ($request->status !== 'pending') {
            throw ValidationException::withMessages(['request' => 'Ombi hili tayari limekaguliwa.']);
        }
        return DB::transaction(function () use ($request, $data, $userId) {
            if ($data['decision'] === 'rejected') {
                $request->update(['status' => 'rejected', 'reviewed_by' => $userId, 'reviewed_at' => now(), 'review_notes' => $data['review_notes']]);
                return $request;
            }
            $approvedAny = false;
            foreach ($request->items as $item) {
                $quantity = (float) ($data['approved'][$item->id] ?? 0);
                if ($quantity > (float) $item->requested_packages) {
                    throw ValidationException::withMessages(['approved' => 'Kiasi kilichoidhinishwa hakiwezi kuzidi kilichoombwa.']);
                }
                $item->update(['approved_packages' => $quantity]);
                $approvedAny = $approvedAny || $quantity > 0;
            }
            if (!$approvedAny) {
                throw ValidationException::withMessages(['approved' => 'Idhinisha angalau bidhaa moja.']);
            }
            $request->update(['status' => 'approved', 'reviewed_by' => $userId, 'reviewed_at' => now(), 'review_notes' => $data['review_notes'] ?? null]);
            return $request;
        });
    }

    public function createOrder(StockRequest $request, array $data, int $userId): PurchaseOrder
    {
        if ($request->status !== 'approved' || $request->purchaseOrder) {
            throw ValidationException::withMessages(['request' => 'Ombi hili halipo tayari kuagizwa.']);
        }
        return DB::transaction(function () use ($request, $data, $userId) {
            $order = PurchaseOrder::create([
                'order_number' => $this->number('PO'), 'stock_request_id' => $request->id,
                'business_id' => $request->business_id, 'supplier' => $data['supplier'] ?? null,
                'order_date' => $data['order_date'], 'notes' => $data['notes'] ?? null, 'ordered_by' => $userId,
            ]);
            foreach ($request->items->where('approved_packages', '>', 0) as $item) {
                $cost = (float) ($data['costs'][$item->id] ?? 0);
                $order->items()->create([
                    'product_id' => $item->product_id, 'ordered_packages' => $item->approved_packages,
                    'units_per_package' => $item->units_per_package, 'cost_per_package' => $cost,
                    'total_cost' => $cost * (float) $item->approved_packages,
                ]);
            }
            $request->update(['status' => 'ordered']);
            return $order;
        });
    }

    public function receive(PurchaseOrder $order, array $data, int $userId): GoodsReceipt
    {
        if (!in_array($order->status, ['ordered', 'partially_received'], true)) {
            throw ValidationException::withMessages(['order' => 'Order hii haiwezi kupokelewa tena.']);
        }
        return DB::transaction(function () use ($order, $data, $userId) {
            $receipt = GoodsReceipt::create([
                'receipt_number' => $this->number('GRN'), 'purchase_order_id' => $order->id,
                'receipt_date' => $data['receipt_date'], 'notes' => $data['notes'] ?? null, 'received_by' => $userId,
            ]);
            $receivedAny = false;
            foreach ($order->items as $item) {
                $quantity = (float) ($data['received'][$item->id] ?? 0);
                $already = (float) $item->receiptItems()->sum('received_packages');
                if ($quantity < 0 || $already + $quantity > (float) $item->ordered_packages) {
                    throw ValidationException::withMessages(['received' => 'Kiasi kilichopokelewa hakiwezi kuzidi order iliyobaki.']);
                }
                if ($quantity <= 0) continue;
                $receivedAny = true;
                $units = $quantity * (float) $item->units_per_package;
                $purchase = Purchase::create([
                    'product_id' => $item->product_id, 'business_id' => $order->business_id,
                    'quantity' => $units, 'unit_cost' => $item->cost_per_package / $item->units_per_package,
                    'total_cost' => $quantity * $item->cost_per_package, 'date' => $data['receipt_date'],
                    'supplier' => $order->supplier, 'notes' => 'Generated from '.$receipt->receipt_number,
                ]);
                $receipt->items()->create(['purchase_order_item_id' => $item->id, 'received_packages' => $quantity, 'purchase_id' => $purchase->id]);
            }
            if (!$receivedAny) throw ValidationException::withMessages(['received' => 'Weka kiasi cha bidhaa angalau moja kilichopokelewa.']);
            $complete = $order->items->every(fn ($item) => (float) $item->receiptItems()->sum('received_packages') >= (float) $item->ordered_packages);
            $order->update(['status' => $complete ? 'received' : 'partially_received']);
            $order->stockRequest?->update(['status' => $complete ? 'received' : 'partially_received']);
            return $receipt;
        });
    }

    private function number(string $prefix): string
    {
        return $prefix.'-'.now()->format('Ymd').'-'.strtoupper(Str::random(8));
    }
}
