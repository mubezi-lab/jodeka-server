<?php

use App\Models\Business;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseOrder;
use App\Models\Role;
use App\Models\StockRequest;
use App\Models\User;
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
        'order_date'=>'2026-09-02','supplier'=>'Supplier A','costs'=>[$stockRequest->items()->first()->id=>30000],
    ])->assertRedirect();

    $order = PurchaseOrder::firstOrFail();
    $this->actingAs($employee)->post(route('procurement.orders.receive',$order), [
        'receipt_date'=>'2026-09-02','received'=>[$order->items()->first()->id=>3],
    ])->assertRedirect();

    expect($order->fresh()->status)->toBe('partially_received')
        ->and(Purchase::count())->toBe(1)
        ->and((float) Purchase::first()->quantity)->toBe(60.0)
        ->and((float) Purchase::first()->total_cost)->toBe(90000.0);
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
