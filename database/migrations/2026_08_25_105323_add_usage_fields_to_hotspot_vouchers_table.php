<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hotspot_vouchers', function (Blueprint $table) {
            $table->unsignedBigInteger('bytes_in')->default(0)->after('used_by_mac');
            $table->unsignedBigInteger('bytes_out')->default(0)->after('bytes_in');

            $table->unsignedBigInteger('packets_in')->default(0)->after('bytes_out');
            $table->unsignedBigInteger('packets_out')->default(0)->after('packets_in');

            $table->string('mikrotik_uptime')->nullable()->after('packets_out');

            $table->timestamp('last_synced_at')->nullable()->after('mikrotik_uptime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotspot_vouchers', function (Blueprint $table) {
            $table->dropColumn([
                'bytes_in',
                'bytes_out',
                'packets_in',
                'packets_out',
                'mikrotik_uptime',
                'last_synced_at',
            ]);
        });
    }
};