<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['code' => '1000', 'name' => 'Cash and Cash Equivalents', 'account_type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1100', 'name' => 'Accounts Receivable', 'account_type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1200', 'name' => 'Inventory', 'account_type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '2000', 'name' => 'Accounts Payable', 'account_type' => 'liability', 'normal_balance' => 'credit'],
            ['code' => '3000', 'name' => 'Owner Capital', 'account_type' => 'equity', 'normal_balance' => 'credit'],
            ['code' => '4000', 'name' => 'Sales Revenue', 'account_type' => 'revenue', 'normal_balance' => 'credit'],
            ['code' => '4100', 'name' => 'Other Income', 'account_type' => 'revenue', 'normal_balance' => 'credit'],
            ['code' => '5000', 'name' => 'Cost of Goods Sold', 'account_type' => 'expense', 'normal_balance' => 'debit'],
            ['code' => '5100', 'name' => 'Operating Expenses', 'account_type' => 'expense', 'normal_balance' => 'debit'],
            ['code' => '5200', 'name' => 'Stock Variance', 'account_type' => 'expense', 'normal_balance' => 'debit'],
        ];

        foreach ($accounts as $account) {
            Account::updateOrCreate(
                ['code' => $account['code']],
                $account + ['is_system' => true, 'is_active' => true]
            );
        }
    }
}
