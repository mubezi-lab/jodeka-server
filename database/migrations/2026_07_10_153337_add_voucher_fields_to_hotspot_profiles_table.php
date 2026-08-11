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
        Schema::table('hotspot_profiles', function (Blueprint $table) {

            // Tutatumia hizi badala ya validity_hours
            $table->unsignedInteger('validity_value')
                ->default(1)
                ->after('price');

            $table->enum('validity_unit', [
                'minutes',
                'hours',
                'days',
                'weeks',
                'months'
            ])
                ->default('hours')
                ->after('validity_value');

            // Prefix ya voucher mfano JDK2, JDK5, JDK10
            $table->string('voucher_prefix', 20)
                ->nullable()
                ->after('validity_unit');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotspot_profiles', function (Blueprint $table) {

            $table->dropColumn([
                'validity_value',
                'validity_unit',
                'voucher_prefix',
            ]);

        });
    }
};