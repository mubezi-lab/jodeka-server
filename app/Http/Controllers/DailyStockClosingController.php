<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesBusinessAccess;
use App\Models\DailyStockClosing;
use App\Models\DailyStockClosingItem;
use App\Models\InventoryReorderPolicy;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockRequest;
use App\Services\DailyStockClosingService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DailyStockClosingController extends Controller
{
    use AuthorizesBusinessAccess;

    public function __construct(private DailyStockClosingService $service) {}

    public function index(Request $request)
    {
        $businesses = $this->accessibleBusinesses()->orderBy('name')->get();
        $isEmployee = $request->user()->role?->name === 'employee';
        $selectedBusinessId = $isEmployee ? $request->user()->business_id : ($request->integer('business_id') ?: $businesses->first()?->id);
        if ($selectedBusinessId) $this->authorizeBusiness((int) $selectedBusinessId);

        $month = preg_match('/^\d{4}-\d{2}$/', (string) $request->query('month')) ? $request->query('month') : now()->format('Y-m');
        $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $closingQuery = DailyStockClosing::with(['business','recorder','items','autoRequest'])
            ->when($selectedBusinessId, fn ($q) => $q->where('business_id', $selectedBusinessId))
            ->whereBetween('closing_date', [$monthStart->toDateString(), $monthEnd->toDateString()]);
        $closings = (clone $closingQuery)->latest('closing_date')->get();
        $monthlyTotals = [
            'revenue' => $closings->sum(fn ($closing) => $closing->items->sum('revenue')),
            'cogs' => $closings->sum(fn ($closing) => $closing->items->sum('cost_of_goods_sold')),
            'profit' => $closings->sum(fn ($closing) => $closing->items->sum('gross_profit')),
        ];

        return view('daily-stock.index', compact('businesses','selectedBusinessId','isEmployee','closings','month','monthlyTotals'));
    }

    public function context(Request $request)
    {
        if ($request->user()->role?->name === 'employee') {
            $request->merge(['business_id' => $request->user()->business_id, 'closing_date' => now()->toDateString()]);
        }
        $validated = $request->validate(['business_id' => ['required','integer','exists:businesses,id'], 'closing_date' => ['required','date','before_or_equal:today']]);
        $businessId = $request->user()->role?->name === 'employee' ? (int) $request->user()->business_id : (int) $validated['business_id'];
        $date = $request->user()->role?->name === 'employee' ? now()->toDateString() : $validated['closing_date'];
        $this->authorizeBusiness($businessId);

        $policies = InventoryReorderPolicy::with('product')->where('business_id', $businessId)->where('is_active', true)->get();
        $items = $policies->map(function ($policy) use ($businessId, $date) {
            $previous = DailyStockClosingItem::where('product_id', $policy->product_id)
                ->whereHas('closing', fn ($q) => $q->where('business_id', $businessId)->whereDate('closing_date', '<', $date))
                ->with('closing')->latest('daily_stock_closing_id')->first();
            $purchases = Purchase::where('business_id', $businessId)->where('product_id', $policy->product_id)->whereDate('date', '<=', $date);
            if ($previous) {
                $purchases->whereDate('date', '>', $previous->closing->closing_date);
            } else {
                $purchases->whereDate('date', $date);
            }
            return [
                'product_id' => $policy->product_id,
                'name' => $policy->product->name,
                'units_per_package' => (float) $policy->product->units_per_package,
                'opening_units' => $previous ? (float) $previous->closing_units : 0,
                'has_previous' => (bool) $previous,
                'received_units' => (float) (clone $purchases)->sum('quantity'),
                'selling_price' => $previous ? (float) $previous->selling_price : (float) $policy->product->sell_price_per_unit,
                'opening_cost_per_unit' => $previous ? (float) $previous->average_cost_per_unit : (float) $policy->product->buy_price_per_unit,
                'minimum_units' => (float) $policy->minimum_units,
                'target_units' => (float) $policy->target_units,
            ];
        })->values();
        return response()->json(['business_id' => $businessId, 'closing_date' => $date, 'items' => $items]);
    }

    public function store(Request $request)
    {
        $isEmployee = $request->user()->role?->name === 'employee';
        if ($isEmployee) {
            abort_unless($request->user()->business_id, 403, 'Mhudumu hajapangiwa branch.');
            $request->merge(['business_id' => $request->user()->business_id, 'closing_date' => now()->toDateString()]);
        }
        $data = $request->validate([
            'business_id' => ['required','integer','exists:businesses,id'],
            'closing_date' => ['required','date','before_or_equal:today'],
            'notes' => ['nullable','string','max:2000'],
            'items' => ['required','array','min:1'],
            'items.*.product_id' => ['required','integer','distinct','exists:products,id'],
            'items.*.opening_units' => ['nullable','numeric','min:0'],
            'items.*.opening_cost_per_unit' => ['nullable','numeric','min:0'],
            'items.*.closing_units' => ['required','numeric','min:0'],
            'items.*.selling_price' => ['required','numeric','min:0'],
        ]);
        $data['business_id'] = $isEmployee ? (int) $request->user()->business_id : (int) $data['business_id'];
        $data['closing_date'] = $isEmployee ? now()->toDateString() : $data['closing_date'];
        $this->authorizeBusiness($data['business_id']);

        $allowedProducts = InventoryReorderPolicy::where('business_id', $data['business_id'])->where('is_active', true)->pluck('product_id');
        abort_unless(collect($data['items'])->pluck('product_id')->every(fn ($id) => $allowedProducts->contains($id)), 422, 'Bidhaa haijawezeshwa kwa branch hii.');

        $closing = $this->service->close($data, $request->user()->id);
        return redirect()->route('daily-stock.show', $closing)->with('success', $closing->autoRequest ? 'Stock imefungwa na auto request imeandaliwa.' : 'Stock ya siku imefungwa.');
    }

    public function show(DailyStockClosing $dailyStock)
    {
        $this->authorizeBusiness($dailyStock->business_id);
        $dailyStock->load(['business','recorder','items.product','autoRequest.items.product']);
        return view('daily-stock.show', compact('dailyStock'));
    }

    public function savePolicies(Request $request)
    {
        abort_unless(in_array($request->user()->role?->name, ['admin','manager'], true), 403);
        $data = $request->validate([
            'business_id' => ['required','integer','exists:businesses,id'],
            'policies' => ['required','array','min:1'],
            'policies.*.product_id' => ['required','integer','distinct','exists:products,id'],
            'policies.*.minimum_units' => ['required','numeric','min:0'],
            'policies.*.target_units' => ['required','numeric','min:0'],
            'policies.*.is_active' => ['nullable','boolean'],
        ]);
        $this->authorizeBusiness((int) $data['business_id']);
        foreach ($data['policies'] as $row) {
            if ((float) $row['target_units'] < (float) $row['minimum_units']) {
                return back()->withErrors(['policies' => 'Target stock haiwezi kuwa chini ya minimum stock.'])->withInput();
            }
        }
        foreach ($data['policies'] as $row) {
            InventoryReorderPolicy::updateOrCreate(
                ['business_id' => $data['business_id'], 'product_id' => $row['product_id']],
                ['minimum_units' => $row['minimum_units'], 'target_units' => $row['target_units'], 'is_active' => (bool) ($row['is_active'] ?? false), 'updated_by' => $request->user()->id]
            );
        }
        return back()->with('success', 'Stock limits zimehifadhiwa.');
    }

    public function policies(Request $request)
    {
        abort_unless(in_array($request->user()->role?->name, ['admin','manager'], true), 403);
        $businessId = $request->integer('business_id');
        $this->authorizeBusiness($businessId);
        $existing = InventoryReorderPolicy::where('business_id', $businessId)->get()->keyBy('product_id');
        return response()->json(Product::orderBy('name')->get()->map(fn ($product) => [
            'product_id' => $product->id, 'name' => $product->name,
            'units_per_package' => (float) $product->units_per_package,
            'minimum_units' => (float) ($existing->get($product->id)?->minimum_units ?? 0),
            'target_units' => (float) ($existing->get($product->id)?->target_units ?? 0),
            'is_active' => (bool) ($existing->get($product->id)?->is_active ?? false),
        ])->values());
    }

    public function confirmAuto(Request $request, StockRequest $stockRequest)
    {
        abort_unless($stockRequest->source === 'auto' && $stockRequest->status === 'draft', 422);
        $this->authorizeBusiness($stockRequest->business_id);
        $stockRequest->update(['status' => 'pending', 'confirmed_at' => now()]);
        return redirect()->route('procurement.requests.show', $stockRequest)->with('success', 'Auto request imethibitishwa na kutumwa kwa ukaguzi.');
    }
}
