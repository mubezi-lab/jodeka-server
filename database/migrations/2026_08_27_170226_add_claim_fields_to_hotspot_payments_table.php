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
        Schema::table('hotspot_payments', function (Blueprint $table) {
            $table->dateTime('claimed_at')
                ->nullable()
                ->after('voucher_id');

            $table->string('claimed_by_mac', 50)
                ->nullable()
                ->after('claimed_at');

            $table->string('claimed_by_ip', 50)
                ->nullable()
                ->after('claimed_by_mac');

            $table->index('claimed_at');
            $table->index('claimed_by_mac');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotspot_payments', function (Blueprint $table) {
            $table->dropIndex(['claimed_at']);
            $table->dropIndex(['claimed_by_mac']);

            $table->dropColumn([
                'claimed_at',
                'claimed_by_mac',
                'claimed_by_ip',
            ]);
        });
    }
};