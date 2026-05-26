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
        Schema::create('toilet_daily_entries', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | TOILET
            |--------------------------------------------------------------------------
            */

            $table->foreignId('toilet_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | CASH FLOW
            |--------------------------------------------------------------------------
            */

            $table->decimal('opening_balance', 12, 2)
                ->default(0);

            $table->decimal('closing_balance', 12, 2)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | TOTALS
            |--------------------------------------------------------------------------
            */

            $table->decimal('total_expenses', 12, 2)
                ->default(0);

            $table->decimal('total_revenue', 12, 2)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | NOTES
            |--------------------------------------------------------------------------
            */

            $table->text('note')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | DATE
            |--------------------------------------------------------------------------
            */

            $table->date('entry_date');

            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_closed')
                ->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('toilet_daily_entries');
    }
};