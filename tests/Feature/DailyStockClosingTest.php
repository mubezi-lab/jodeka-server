<?php

use App\Models\Business;
use App\Models\DailyStockClosing;
use App\Models\InventoryReorderPolicy;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Role;
use App\Models\StockRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function dailyStockEmployee(Business $business): User
{
    $role = Role::firstOrCreate(['name' => 'employee']);
    $user = User::factory()->create(['business_id' => $business->id]);
    $user->forceFill(['role_id' => $role->id, 'business_id' => $business->id])->save();
    $user->unsetRelation('role');
    $user->businesses()->attach($business->id, ['access_level'=>'employee','is_primary'=>true,'is_active'=>true]);
    return $user;
}

test('daily closing snapshots revenue cost profit and creates auto request', function () {
    $bar = Business::create(['name'=>'Bar','type'=>'bar']);
    $employee = dailyStockEmployee($bar);
    $soda = Product::create([
        'name'=>'Soda','package_type'=>'crate','units_per_package'=>24,
        'buy_price_per_package'=>7200,'buy_price_per_unit'=>300,
        'sell_price_per_unit'=>500,'sell_price_per_package'=>12000,
    ]);
    InventoryReorderPolicy::create([
        'business_id'=>$bar->id,'product_id'=>$soda->id,
        'minimum_units'=>20,'target_units'=>48,'is_active'=>true,
    ]);
    Purchase::create([
        'business_id'=>$bar->id,'product_id'=>$soda->id,'quantity'=>24,
        'unit_cost'=>300,'total_cost'=>7200,'date'=>now()->toDateString(),
    ]);

    $this->actingAs($employee)->post(route('daily-stock.store'), [
        'business_id'=>999,
        'closing_date'=>'2026-01-01',
        'items'=>[[
            'product_id'=>$soda->id,
            'opening_units'=>24,
            'opening_cost_per_unit'=>200,
            'closing_units'=>10,
            'selling_price'=>500,
        ]],
    ])->assertRedirect();

    $closing = DailyStockClosing::firstOrFail();
    $item = $closing->items()->firstOrFail();
    expect($closing->business_id)->toBe($bar->id)
        ->and($closing->closing_date->toDateString())->toBe(now()->toDateString())
        ->and((float)$item->available_units)->toBe(48.0)
        ->and((float)$item->sold_units)->toBe(38.0)
        ->and((float)$item->average_cost_per_unit)->toBe(250.0)
        ->and((float)$item->revenue)->toBe(19000.0)
        ->and((float)$item->cost_of_goods_sold)->toBe(9500.0)
        ->and((float)$item->gross_profit)->toBe(9500.0);

    $auto = StockRequest::where('source','auto')->firstOrFail();
    expect($auto->status)->toBe('draft')
        ->and((float)$auto->items()->first()->requested_packages)->toBe(2.0);

    $this->actingAs($employee)
        ->get(route('procurement.index', ['source'=>'auto', 'status'=>'draft', 'month'=>now()->format('Y-m')]))
        ->assertOk()
        ->assertSee($auto->request_number)
        ->assertSee('AUTO');
});

test('incoming requests prevent duplicate auto replenishment', function () {
    $bar = Business::create(['name'=>'Bar','type'=>'bar']);
    $employee = dailyStockEmployee($bar);
    $water = Product::create([
        'name'=>'Water','package_type'=>'carton','units_per_package'=>24,
        'buy_price_per_package'=>4800,'buy_price_per_unit'=>200,
        'sell_price_per_unit'=>400,'sell_price_per_package'=>9600,
    ]);
    InventoryReorderPolicy::create(['business_id'=>$bar->id,'product_id'=>$water->id,'minimum_units'=>20,'target_units'=>48,'is_active'=>true]);
    $pending = StockRequest::create([
        'request_number'=>'REQ-PENDING','business_id'=>$bar->id,'request_date'=>now(),
        'status'=>'pending','source'=>'manual','requested_by'=>$employee->id,
    ]);
    $pending->items()->create(['product_id'=>$water->id,'requested_packages'=>2,'units_per_package'=>24]);

    $this->actingAs($employee)->post(route('daily-stock.store'), [
        'items'=>[[
            'product_id'=>$water->id,'opening_units'=>24,'opening_cost_per_unit'=>200,
            'closing_units'=>10,'selling_price'=>400,
        ]],
    ])->assertRedirect();

    expect(StockRequest::where('source','auto')->count())->toBe(0);
});
