<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bagambakamo_expenses', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | MEMBER WHO RECEIVED THE MONEY
            |--------------------------------------------------------------------------
            */

            $table->foreignId('member_id')
                ->constrained('bagambakamo_members')
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | EXPENSE DETAILS
            |--------------------------------------------------------------------------
            */

            $table->string('category');

            $table->string('description')
                ->nullable();

            $table->decimal(
                'amount',
                12,
                2
            );

            $table->date('expense_date');

            $table->string('reference')
                ->nullable()
                ->unique();

            $table->text('notes')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'bagambakamo_expenses'
        );
    }
};