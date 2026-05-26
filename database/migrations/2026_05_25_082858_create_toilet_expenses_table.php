<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run migrations.
     */
    public function up(): void
    {
        Schema::create('toilet_expenses', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | DAILY ENTRY
            |--------------------------------------------------------------------------
            */

            $table->foreignId('toilet_daily_entry_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | EXPENSE DETAILS
            |--------------------------------------------------------------------------
            */

            $table->string('expense_name');

            $table->decimal('amount', 12, 2)
                ->default(0);

            $table->text('note')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('toilet_expenses');
    }
};