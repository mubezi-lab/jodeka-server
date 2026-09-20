<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotspot_manual_sms_messages', function (Blueprint $table) {
            $table->foreignId('hotspot_customer_id')
                ->nullable()
                ->after('id')
                ->constrained('hotspot_customers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hotspot_manual_sms_messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('hotspot_customer_id');
        });
    }
};
