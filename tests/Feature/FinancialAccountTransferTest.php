<?php

use App\Models\Account;
use App\Models\Business;
use App\Models\FinancialAccount;
use App\Models\FinancialAccountTransfer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('cash handover moves balance without creating new income', function () {
    Role::insert(['name' => 'admin']);
    $admin = User::factory()->create(['role_id' => Role::where('name', 'admin')->value('id')]);
    $bar = Business::create(['name' => 'Bar', 'type' => 'bar']);
    foreach ([['1000', 'Cash and Cash Equivalents', 'asset', 'debit'], ['3000', 'Owner Capital', 'equity', 'credit']] as [$code, $name, $type, $normal]) {
        Account::create(['code' => $code, 'name' => $name, 'account_type' => $type, 'normal_balance' => $normal, 'is_system' => true, 'is_active' => true]);
    }
    $this->actingAs($admin)->post(route('financial-accounts.store'), [
        'business_id' => $bar->id, 'name' => 'Bar Cash Pending Handover', 'account_type' => 'clearing',
        'opening_balance' => 20000, 'opening_balance_date' => '2026-09-02',
    ])->assertRedirect();
    $this->actingAs($admin)->post(route('financial-accounts.store'), [
        'name' => 'Main Cash', 'account_type' => 'cash', 'opening_balance' => 0,
        'opening_balance_date' => '2026-09-02',
    ])->assertRedirect();
    $branchCash = FinancialAccount::where('name', 'Bar Cash Pending Handover')->firstOrFail();
    $mainCash = FinancialAccount::where('name', 'Main Cash')->firstOrFail();

    $this->actingAs($admin)->post(route('financial-account-transfers.store'), [
        'from_financial_account_id' => $branchCash->id,
        'to_financial_account_id' => $mainCash->id,
        'amount' => 15000,
        'transfer_date' => '2026-09-02',
    ])->assertRedirect();

    $transfer = FinancialAccountTransfer::firstOrFail();
    expect($transfer->status)->toBe('pending')
        ->and($transfer->journal_id)->toBeNull()
        ->and($branchCash->fresh()->current_balance)->toBe(20000.0)
        ->and($mainCash->fresh()->current_balance)->toBe(0.0);

    $this->actingAs($admin)->post(route('financial-account-transfers.confirm', $transfer), [
        'confirmed_amount' => 15000,
    ])->assertRedirect();

    $transfer->refresh();
    expect($transfer->journal->entries()->sum('debit'))->toEqual(15000)
        ->and($transfer->journal->entries()->sum('credit'))->toEqual(15000)
        ->and($transfer->status)->toBe('confirmed')
        ->and($transfer->variance)->toBe('0.00')
        ->and($branchCash->fresh()->current_balance)->toBe(5000.0)
        ->and($mainCash->fresh()->current_balance)->toBe(15000.0);
});

test('cash handover cannot exceed the source account balance', function () {
    Role::insert(['name' => 'admin']);
    $admin = User::factory()->create(['role_id' => Role::where('name', 'admin')->value('id')]);
    foreach ([['1000', 'Cash and Cash Equivalents', 'asset', 'debit'], ['3000', 'Owner Capital', 'equity', 'credit']] as [$code, $name, $type, $normal]) {
        Account::create(['code' => $code, 'name' => $name, 'account_type' => $type, 'normal_balance' => $normal, 'is_system' => true, 'is_active' => true]);
    }
    $this->actingAs($admin)->post(route('financial-accounts.store'), ['name' => 'Source', 'account_type' => 'cash', 'opening_balance' => 1000])->assertRedirect();
    $this->actingAs($admin)->post(route('financial-accounts.store'), ['name' => 'Destination', 'account_type' => 'cash', 'opening_balance' => 0])->assertRedirect();
    $from = FinancialAccount::where('name', 'Source')->firstOrFail();
    $to = FinancialAccount::where('name', 'Destination')->firstOrFail();

    $this->actingAs($admin)->post(route('financial-account-transfers.store'), [
        'from_financial_account_id' => $from->id,
        'to_financial_account_id' => $to->id,
        'amount' => 1500,
        'transfer_date' => '2026-09-02',
    ])->assertSessionHasErrors('amount');

    expect(FinancialAccountTransfer::count())->toBe(0);
});
