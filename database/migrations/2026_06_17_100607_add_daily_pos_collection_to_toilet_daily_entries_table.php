<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('toilet_daily_entries', function (Blueprint $table) {

            $table->decimal(
                'daily_pos_collection',
                15,
                2
            )->default(0)
             ->after('pos_amount');

        });
    }

    public function down(): void
    {
        Schema::table('toilet_daily_entries', function (Blueprint $table) {

            $table->dropColumn(
                'daily_pos_collection'
            );

        });
    }
};