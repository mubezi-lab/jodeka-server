<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesBusinessAccess;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockRequest;
use App\Models\Supplier;
use App\Models\FinancialAccount;
use App\Services\ProcurementService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProcurementController extends Controller
{
    use AuthorizesBusinessAccess;

    public function __construct(private ProcurementService $service) {}

    public function index()
    {
        $user = request()->user();
        $role = $user->role?->name;
        $businesses = $this->accessibleBusinesses()->orderBy('name')->get();
        $businessIds = $businesses->pluck('id');
        $fixedBusiness = $role === 'employee' ? $businesses->firstWhere('id', $user->business_id) ?? $businesses->first() : null;

        return view('procurement.index', [
            'businesses' => $businesses,
            'fixedBusiness' => $fixedBusiness,
            'canBackdate' => in_array($role, ['admin', 'manager'], true),
            'products' => Product::orderBy('name')->get(),
            'suppliers' => Supplier::where('is_active',true)->orderBy('name')->get(),
            'financialAccounts' => FinancialAccount::where('is_active',true)->orderBy('name')->get(),
            'requests' => StockRequest::with(['business','requester','items.product','purchaseOrder'])
                ->whereIn('business_id', $businessIds)->latest()->paginate(20),
        ]);
    }

    public function storeRequest(Request $request)
    {
        $user = $request->user();
        $role = $user->role?->name;
        $data = $request->validate([
            'business_id' => ['nullable','integer','exists:businesses,id'],
            'request_date' => ['nullable','date','before_or_equal:today'],
            'notes' => ['nullable','string'],
            'items' => ['required','array','min:1'], 'items.*.product_id' => ['required','distinct','exists:products,id'],
            'items.*.quantity' => ['required','numeric','gt:0'], 'items.*.notes' => ['nullable','string'],
        ]);

        if ($role === 'employee') {
            $businessId = (int) ($user->business_id ?: $this->accessibleBusinesses()->value('id'));
            abort_unless($businessId > 0, 403, 'Mhudumu hajapangiwa branch.');
            if (!empty($data['business_id']) && (int) $data['business_id'] !== $businessId) {
                abort(403, 'Huna ruhusa ya kutumia branch hii.');
            }
            $data['business_id'] = $businessId;
            $data['request_date'] = now()->toDateString();
        } else {
            if (empty($data['business_id']) || empty($data['request_date'])) {
                return back()->withErrors(['business_id' => 'Chagua branch na tarehe ya request.'])->withInput();
            }
            $this->authorizeBusiness((int) $data['business_id']);
        }

        $this->service->createRequest($data, $user->id);
        return back()->with('success', 'Ombi la stock limetumwa.');
    }

    public function show(StockRequest $stockRequest)
    {
        $this->authorizeBusiness($stockRequest->business_id);
        $stockRequest->load(['business','requester','reviewer','items.product','purchaseOrder.items.product','purchaseOrder.receipts.items']);
        $productIds = $stockRequest->items->pluck('product_id');
        $lastPackageCosts = PurchaseOrderItem::query()
            ->whereIn('product_id', $productIds)
            ->whereHas('purchaseOrder', fn ($query) => $query->where('business_id', $stockRequest->business_id))
            ->whereHas('receiptItems')
            ->latest('id')
            ->get()
            ->unique('product_id')
            ->mapWithKeys(fn ($item) => [$item->product_id => (float) $item->cost_per_package]);

        foreach ($stockRequest->items as $item) {
            if (!$lastPackageCosts->has($item->product_id) && (float) $item->product->buy_price_per_package > 0) {
                $lastPackageCosts->put($item->product_id, (float) $item->product->buy_price_per_package);
            }
        }

        return view('procurement.show', [
            'stockRequest'=>$stockRequest,
            'suppliers'=>Supplier::where('is_active',true)->orderBy('name')->get(),
            'financialAccounts'=>FinancialAccount::where('is_active',true)->orderBy('name')->get(),
            'lastPackageCosts'=>$lastPackageCosts,
        ]);
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
            'order_date' => ['required','date','before_or_equal:today'], 'supplier_id' => ['nullable','exists:suppliers,id','required_if:payment_type,credit'],
            'supplier' => ['nullable','string','max:255'], 'payment_type' => ['required', Rule::in(['cash','credit'])],
            'payment_financial_account_id' => ['nullable','exists:financial_accounts,id','required_if:payment_type,cash'], 'notes' => ['nullable','string'],
            'costs' => ['required','array'], 'costs.*' => ['required','numeric','min:0'],
        ]);
        if (!empty($data['payment_financial_account_id'])) {
            $account = FinancialAccount::findOrFail($data['payment_financial_account_id']);
            if ($account->business_id) $this->authorizeBusiness($account->business_id);
        }
        $this->service->createOrder($stockRequest->load(['items','purchaseOrder']), $data, $request->user()->id);
        return back()->with('success', 'Purchase order imetengenezwa.');
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorizeBusiness($purchaseOrder->business_id);
        $data = $request->validate([
            'receipt_date' => ['required','date','before_or_equal:today'], 'notes' => ['nullable','string'], 'received' => ['required','array'],
            'received.*' => ['nullable','numeric','min:0'],
        ]);
        $this->service->receive($purchaseOrder->load(['items.receiptItems','stockRequest']), $data, $request->user()->id);
        return back()->with('success', 'Bidhaa zilizopokelewa zimeongezwa kwenye purchases/stock flow.');
    }
}
