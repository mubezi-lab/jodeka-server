<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotspot_customers', function (Blueprint $table) {
            $table->string('payment_name')->nullable()->after('name');
            $table->string('name_source', 20)->default('manual')->after('payment_name');
            $table->index('payment_name');
        });

        DB::table('hotspot_customers')
            ->orderBy('id')
            ->chunkById(200, function ($customers) {
                foreach ($customers as $customer) {
                    $paymentName = DB::table('hotspot_payments')
                        ->where('hotspot_customer_id', $customer->id)
                        ->whereNotNull('payer_name')
                        ->where('payer_name', '<>', '')
                        ->orderByDesc('paid_at')
                        ->orderByDesc('id')
                        ->value('payer_name');

                    if (! $paymentName) {
                        continue;
                    }

                    $customerName = trim((string) $customer->name);
                    $nameSource = $customerName !== ''
                        && strcasecmp($customerName, trim((string) $paymentName)) === 0
                            ? 'payment'
                            : 'manual';

                    DB::table('hotspot_customers')
                        ->where('id', $customer->id)
                        ->update([
                            'payment_name' => $paymentName,
                            'name_source' => $nameSource,
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('hotspot_customers', function (Blueprint $table) {
            $table->dropIndex(['payment_name']);
            $table->dropColumn(['payment_name', 'name_source']);
        });
    }
};
