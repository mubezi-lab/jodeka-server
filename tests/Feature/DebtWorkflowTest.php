<?php

use App\Models\Account;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Debt;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can create a debt and receive a partial payment with balanced journals', function () {
    Role::insert(['name' => 'admin']);
    $role = Role::where('name', 'admin')->firstOrFail();
    $admin = User::factory()->create(['role_id' => $role->id]);
    $business = Business::create(['name' => 'Bar', 'type' => 'bar']);
    $customer = Customer::create([
        'customer_number' => 'CUS-TEST-1',
        'name' => 'Test Customer',
        'is_active' => true,
        'created_by' => $admin->id,
    ]);

    foreach ([
        ['1100', 'Accounts Receivable', 'asset', 'debit'],
        ['4000', 'Sales Revenue', 'revenue', 'credit'],
        ['1000', 'Cash and Cash Equivalents', 'asset', 'debit'],
    ] as [$code, $name, $type, $normalBalance]) {
        Account::create([
            'code' => $code, 'name' => $name, 'account_type' => $type,
            'normal_balance' => $normalBalance, 'is_system' => true, 'is_active' => true,
        ]);
    }

    $this->actingAs($admin)->post(route('debts.store'), [
        'customer_id' => $customer->id,
        'business_id' => $business->id,
        'original_amount' => 30000,
        'debt_date' => '2026-09-02',
        'due_date' => '2026-09-10',
        'description' => 'Bia 15',
    ])->assertRedirect(route('debts.index'));

    $debt = Debt::firstOrFail();
    expect($debt->balance)->toBe('30000.00')
        ->and($debt->journal->entries()->sum('debit'))->toEqual(30000)
        ->and($debt->journal->entries()->sum('credit'))->toEqual(30000);

    $this->actingAs($admin)->post(route('debts.payments.store', $debt), [
        'amount' => 10000,
        'payment_date' => '2026-09-03',
        'payment_method' => 'cash',
    ])->assertRedirect();

    $debt->refresh();
    $payment = $debt->payments()->firstOrFail();
    expect($debt->balance)->toBe('20000.00')
        ->and($debt->status)->toBe('partial')
        ->and($payment->journal->entries()->sum('debit'))->toEqual(10000)
        ->and($payment->journal->entries()->sum('credit'))->toEqual(10000);
});

test('employee cannot record a debt for an unassigned business', function () {
    Role::insert(['name' => 'employee']);
    $role = Role::where('name', 'employee')->firstOrFail();
    $employee = User::factory()->create(['role_id' => $role->id]);
    $assigned = Business::create(['name' => 'Duka', 'type' => 'retail']);
    $other = Business::create(['name' => 'Bar', 'type' => 'bar']);
    $employee->businesses()->attach($assigned->id, [
        'access_level' => 'employee', 'is_primary' => true, 'is_active' => true,
    ]);
    $customer = Customer::create([
        'customer_number' => 'CUS-TEST-2', 'name' => 'Another Customer',
        'is_active' => true, 'created_by' => $employee->id,
    ]);

    $this->actingAs($employee)->post(route('debts.store'), [
        'customer_id' => $customer->id,
        'business_id' => $other->id,
        'original_amount' => 5000,
        'debt_date' => '2026-09-02',
    ])->assertSessionHasErrors('business_id');

    expect(Debt::count())->toBe(0);
});
