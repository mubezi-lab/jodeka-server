<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesBusinessAccess;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockRequest;
use App\Services\ProcurementService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProcurementController extends Controller
{
    use AuthorizesBusinessAccess;

    public function __construct(private ProcurementService $service) {}

    public function index()
    {
        $businessIds = $this->accessibleBusinesses()->pluck('id');
        return view('procurement.index', [
            'businesses' => $this->accessibleBusinesses()->orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
            'requests' => StockRequest::with(['business','requester','items.product','purchaseOrder'])
                ->whereIn('business_id', $businessIds)->latest()->paginate(20),
        ]);
    }

    public function storeRequest(Request $request)
    {
        $data = $request->validate([
            'business_id' => ['required','integer','exists:businesses,id'], 'request_date' => ['required','date'], 'notes' => ['nullable','string'],
            'items' => ['required','array','min:1'], 'items.*.product_id' => ['required','distinct','exists:products,id'],
            'items.*.quantity' => ['required','numeric','gt:0'], 'items.*.notes' => ['nullable','string'],
        ]);
        $this->authorizeBusiness((int) $data['business_id']);
        $this->service->createRequest($data, $request->user()->id);
        return back()->with('success', 'Ombi la stock limetumwa.');
    }

    public function show(StockRequest $stockRequest)
    {
        $this->authorizeBusiness($stockRequest->business_id);
        $stockRequest->load(['business','requester','reviewer','items.product','purchaseOrder.items.product','purchaseOrder.receipts.items']);
        return view('procurement.show', compact('stockRequest'));
    }

    public function review(Request $request, StockRequest $stockRequest)
    {
        abort_unless(in_array($request->user()->role?->name, ['admin','manager'], true), 403);
        $this->authorizeBusiness($stockRequest->business_id);
        $data = $request->validate([
            'decision' => ['required', Rule::in(['approved','rejected'])], 'approved' => ['nullable','array'],
            'approved.*' => ['nullable','numeric','min:0'], 'review_notes' => ['nullable','string','required_if:decision,rejected'],
        ]);
        $this->service->review($stockRequest->load('items'), $data, $request->user()->id);
        return back()->with('success', 'Ombi limekaguliwa.');
    }

    public function order(Request $request, StockRequest $stockRequest)
    {
        abort_unless(in_array($request->user()->role?->name, ['admin','manager'], true), 403);
        $this->authorizeBusiness($stockRequest->business_id);
        $data = $request->validate([
            'order_date' => ['required','date'], 'supplier' => ['nullable','string','max:255'], 'notes' => ['nullable','string'],
            'costs' => ['required','array'], 'costs.*' => ['required','numeric','min:0'],
        ]);
        $this->service->createOrder($stockRequest->load(['items','purchaseOrder']), $data, $request->user()->id);
        return back()->with('success', 'Purchase order imetengenezwa.');
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorizeBusiness($purchaseOrder->business_id);
        $data = $request->validate([
            'receipt_date' => ['required','date'], 'notes' => ['nullable','string'], 'received' => ['required','array'],
            'received.*' => ['nullable','numeric','min:0'],
        ]);
        $this->service->receive($purchaseOrder->load(['items.receiptItems','stockRequest']), $data, $request->user()->id);
        return back()->with('success', 'Bidhaa zilizopokelewa zimeongezwa kwenye purchases/stock flow.');
    }
}
