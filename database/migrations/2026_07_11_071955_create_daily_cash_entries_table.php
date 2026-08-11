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
        Schema::create('daily_cash_entries', function (Blueprint $table) {
            $table->id();

            $table->date('entry_date')->unique();

            $table->decimal('yas', 15, 2)->default(0);
            $table->decimal('voda', 15, 2)->default(0);
            $table->decimal('halotel', 15, 2)->default(0);
            $table->decimal('airtel', 15, 2)->default(0);
            $table->decimal('token', 15, 2)->default(0);
            $table->decimal('noti', 15, 2)->default(0);

            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('closing_balance', 15, 2)->default(0);

            $table->decimal('expenses_total', 15, 2)->default(0);
            $table->decimal('external_total', 15, 2)->default(0);

            $table->decimal('shop_income', 15, 2)->default(0);

            $table->text('raw_input')->nullable();

            $table->integer('created_by')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_cash_entries');
    }
};