<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run migrations
     */
    public function up(): void
    {
        Schema::create('savings', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | BUSINESS
            |--------------------------------------------------------------------------
            */

            $table->foreignId('business_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | SAVING TYPE
            |--------------------------------------------------------------------------
            */

            $table->enum('type', [

                'personal',
                'business',
                'group',
                'emergency',
                'investment'

            ]);

            /*
            |--------------------------------------------------------------------------
            | DETAILS
            |--------------------------------------------------------------------------
            */

            $table->string('name');

            $table->text('description')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | FINANCIALS
            |--------------------------------------------------------------------------
            */

            $table->decimal('target_amount', 15, 2)
                ->nullable();

            $table->decimal('balance', 15, 2)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | DATES
            |--------------------------------------------------------------------------
            */

            $table->date('start_date')
                ->nullable();

            $table->date('maturity_date')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [

                'active',
                'completed',
                'closed'

            ])->default('active');

            /*
            |--------------------------------------------------------------------------
            | CREATED BY
            |--------------------------------------------------------------------------
            */

            $table->integer('created_by')
                ->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('savings');
    }
};