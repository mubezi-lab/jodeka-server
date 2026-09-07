<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesBusinessAccess;
use App\Models\FinancialAccount;
use App\Models\Supplier;
use App\Models\SupplierBill;
use App\Services\SupplierAccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupplierController extends Controller
{
    use AuthorizesBusinessAccess;
    public function __construct(private SupplierAccountingService $accounting) {}

    public function index()
    {
        $ids = $this->accessibleBusinesses()->pluck('id');
        return view('suppliers.index', [
            'suppliers'=>Supplier::withSum('bills as outstanding','balance')->orderBy('name')->get(),
            'bills'=>SupplierBill::with(['supplier','business','goodsReceipt.purchaseOrder','payments.financialAccount'])->whereIn('business_id',$ids)->latest()->paginate(20),
            'financialAccounts'=>FinancialAccount::where('is_active',true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(in_array($request->user()->role?->name,['admin','manager'],true),403);
        $data=$request->validate(['name'=>['required','string','max:255'],'phone'=>['nullable','string','max:30'],'email'=>['nullable','email','max:255'],'address'=>['nullable','string']]);
        Supplier::create($data+['supplier_number'=>'SUP-'.now()->format('Ymd').'-'.strtoupper(Str::random(6))]);
        return back()->with('success','Supplier ameongezwa.');
    }

    public function pay(Request $request, SupplierBill $supplierBill)
    {
        abort_unless(in_array($request->user()->role?->name,['admin','manager'],true),403);
        $this->authorizeBusiness($supplierBill->business_id);
        $data=$request->validate(['financial_account_id'=>['required','exists:financial_accounts,id'],'amount'=>['required','numeric','gt:0'],'payment_date'=>['required','date'],'payment_method'=>['required','string','max:30'],'external_reference'=>['nullable','string','max:255'],'notes'=>['nullable','string']]);
        $account = FinancialAccount::findOrFail($data['financial_account_id']);
        if ($account->business_id) $this->authorizeBusiness($account->business_id);
        $this->accounting->pay($supplierBill,$data);
        return back()->with('success','Malipo ya supplier yamehifadhiwa.');
    }
}
