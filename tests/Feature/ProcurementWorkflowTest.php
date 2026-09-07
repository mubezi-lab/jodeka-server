<?php

use App\Models\Business;
use App\Models\Account;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseOrder;
use App\Models\Role;
use App\Models\StockRequest;
use App\Models\Supplier;
use App\Models\SupplierBill;
use App\Models\User;
use App\Services\FinancialAccountOpeningService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('request approval order and receipt only add actually received goods', function () {
    Role::insert([['name' => 'manager'], ['name' => 'employee']]);
    $bar = Business::create(['name' => 'Bar', 'type' => 'bar']);
    $manager = User::factory()->create(['role_id' => Role::where('name','manager')->value('id'), 'business_id' => $bar->id]);
    $employee = User::factory()->create(['role_id' => Role::where('name','employee')->value('id'), 'business_id' => $bar->id]);
    $manager->businesses()->attach($bar->id, ['access_level'=>'manager','is_primary'=>true,'is_active'=>true]);
    $employee->businesses()->attach($bar->id, ['access_level'=>'employee','is_primary'=>true,'is_active'=>true]);
    $beer = Product::create([
        'name'=>'Beer','package_type'=>'crate','units_per_package'=>20,
        'buy_price_per_package'=>30000,'sell_price_per_unit'=>2000,
        'buy_price_per_unit'=>1500,'sell_price_per_package'=>40000,
    ]);
    foreach ([['1000','Cash','asset','debit'],['1200','Inventory','asset','debit'],['2000','Accounts Payable','liability','credit'],['3000','Owner Capital','equity','credit']] as [$code,$name,$type,$normal]) {
        Account::updateOrCreate(['code'=>$code],['name'=>$name,'account_type'=>$type,'normal_balance'=>$normal,'is_system'=>true,'is_active'=>true]);
    }
    $supplier = Supplier::create(['supplier_number'=>'SUP-TEST','name'=>'Supplier A']);

    $this->actingAs($employee)->post(route('procurement.requests.store'), [
        'business_id'=>$bar->id,'request_date'=>'2026-09-02',
        'items'=>[['product_id'=>$beer->id,'quantity'=>5]],
    ])->assertRedirect();
    $stockRequest = StockRequest::firstOrFail();
    expect($stockRequest->status)->toBe('pending');

    $this->actingAs($manager)->post(route('procurement.requests.review',$stockRequest), [
        'decision'=>'approved','approved'=>[$stockRequest->items()->first()->id=>4],
    ])->assertRedirect();
    $this->actingAs($manager)->post(route('procurement.requests.order',$stockRequest), [
        'order_date'=>'2026-09-02','supplier_id'=>$supplier->id,'payment_type'=>'credit','costs'=>[$stockRequest->items()->first()->id=>30000],
    ])->assertRedirect();

    $order = PurchaseOrder::firstOrFail();
    $this->actingAs($employee)->post(route('procurement.orders.receive',$order), [
        'receipt_date'=>'2026-09-02','received'=>[$order->items()->first()->id=>3],
    ])->assertRedirect();

    expect($order->fresh()->status)->toBe('partially_received')
        ->and(Purchase::count())->toBe(1)
        ->and((float) Purchase::first()->quantity)->toBe(60.0)
        ->and((float) Purchase::first()->total_cost)->toBe(90000.0);
    $bill = SupplierBill::firstOrFail();
    expect((float) $bill->balance)->toBe(90000.0)
        ->and($bill->journal->entries()->sum('debit'))->toEqual(90000)
        ->and($bill->journal->entries()->sum('credit'))->toEqual(90000);

    $this->actingAs($manager);
    $cash = app(FinancialAccountOpeningService::class)->create([
        'business_id'=>$bar->id,'name'=>'Bar Cash','account_type'=>'cash',
        'opening_balance'=>100000,'opening_balance_date'=>'2026-09-02',
    ]);
    $this->post(route('supplier-bills.pay',$bill), [
        'financial_account_id'=>$cash->id,'amount'=>40000,'payment_date'=>'2026-09-02','payment_method'=>'cash',
    ])->assertRedirect();
    $bill->refresh();
    expect((float) $bill->balance)->toBe(50000.0)
        ->and($bill->status)->toBe('partial')
        ->and($cash->fresh()->current_balance)->toBe(60000.0)
        ->and($bill->payments()->first()->journal->entries()->sum('debit'))->toEqual(40000)
        ->and($bill->payments()->first()->journal->entries()->sum('credit'))->toEqual(40000);
});

test('employee cannot request stock for an unassigned branch', function () {
    Role::insert(['name'=>'employee']);
    $bar = Business::create(['name'=>'Bar','type'=>'bar']);
    $shop = Business::create(['name'=>'Duka','type'=>'retail']);
    $employee = User::factory()->create([
        'role_id' => Role::where('name', 'employee')->value('id'),
        'business_id' => $bar->id,
    ]);
    $product = Product::create(['name'=>'Soda','package_type'=>'crate','units_per_package'=>24,'buy_price_per_package'=>0,'sell_price_per_unit'=>0,'buy_price_per_unit'=>0,'sell_price_per_package'=>0]);

    $this->actingAs($employee)->post(route('procurement.requests.store'), [
        'business_id'=>$shop->id,'request_date'=>'2026-09-02',
        'items'=>[['product_id'=>$product->id,'quantity'=>1]],
    ])->assertForbidden();
});

test('employee request always uses assigned branch and current date', function () {
    Role::insert(['name'=>'employee']);
    $bar = Business::create(['name'=>'Bar','type'=>'bar']);
    $employeeRoleId = Role::where('name', 'employee')->value('id');
    $employee = User::factory()->create(['business_id'=>$bar->id]);
    $employee->forceFill([
        'role_id' => $employeeRoleId,
        'business_id' => $bar->id,
    ])->save();
    $employee->unsetRelation('role');
    $product = Product::create(['name'=>'Water','package_type'=>'carton','units_per_package'=>12,'buy_price_per_package'=>0,'sell_price_per_unit'=>0,'buy_price_per_unit'=>0,'sell_price_per_package'=>0]);

    $this->actingAs($employee)->post(route('procurement.requests.store'), [
        'business_id'=>$bar->id,
        'request_date'=>'2026-01-01',
        'items'=>[['product_id'=>$product->id,'quantity'=>2]],
    ])->assertRedirect();

    $request = StockRequest::firstOrFail();
    expect($request->business_id)->toBe($bar->id)
        ->and($request->request_date->toDateString())->toBe(now()->toDateString());
});
