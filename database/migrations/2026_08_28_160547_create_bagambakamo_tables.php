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
        /*
        |--------------------------------------------------------------------------
        | BAGAMBAKAMO MEMBERS
        |--------------------------------------------------------------------------
        */

        Schema::create('bagambakamo_members', function (Blueprint $table) {
            $table->id();

            $table->string('full_name');

            $table->string('phone')->nullable();

            $table->enum('status', [
                'active',
                'inactive'
            ])->default('active');

            $table->date('join_date')->nullable();

            $table->timestamps();
        });


        /*
        |--------------------------------------------------------------------------
        | BAGAMBAKAMO CONTRIBUTION TYPES
        |--------------------------------------------------------------------------
        */

        Schema::create('bagambakamo_contribution_types', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->text('description')->nullable();

            $table->timestamps();
        });


        /*
        |--------------------------------------------------------------------------
        | BAGAMBAKAMO CONTRIBUTIONS
        |--------------------------------------------------------------------------
        */

        Schema::create('bagambakamo_contributions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('member_id')
                ->constrained('bagambakamo_members')
                ->cascadeOnDelete();

            $table->foreignId('contribution_type_id')
                ->constrained('bagambakamo_contribution_types')
                ->cascadeOnDelete();

            $table->decimal('amount', 12, 2);

            /*
             * Example:
             * 2026-03
             */
            $table->string('contribution_month');

            $table->enum('status', [
                'paid',
                'pending'
            ])->default('pending');

            $table->timestamps();
        });


        /*
        |--------------------------------------------------------------------------
        | BAGAMBAKAMO PAYMENTS
        |--------------------------------------------------------------------------
        */

        Schema::create('bagambakamo_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('member_id')
                ->constrained('bagambakamo_members')
                ->cascadeOnDelete();

            $table->decimal('amount', 12, 2);

            $table->enum('type', [
                'monthly',
                'mchango'
            ]);

            $table->string('description')->nullable();

            $table->date('payment_date');

            $table->string('method')->nullable();

            $table->string('reference')->nullable();

            $table->timestamps();
        });


        /*
        |--------------------------------------------------------------------------
        | BAGAMBAKAMO MEMBER BALANCES
        |--------------------------------------------------------------------------
        */

        Schema::create('bagambakamo_member_balances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('member_id')
                ->constrained('bagambakamo_members')
                ->cascadeOnDelete();

            $table->decimal(
                'total_contribution',
                12,
                2
            )->default(0);

            $table->decimal(
                'total_paid',
                12,
                2
            )->default(0);

            $table->decimal(
                'balance',
                12,
                2
            )->default(0);

            $table->timestamps();

            /*
             * Member mmoja awe na balance record moja.
             */
            $table->unique('member_id');
        });


        /*
        |--------------------------------------------------------------------------
        | BAGAMBAKAMO GROUP SETTINGS
        |--------------------------------------------------------------------------
        */

        Schema::create('bagambakamo_group_settings', function (Blueprint $table) {
            $table->id();

            $table->decimal(
                'monthly_amount',
                12,
                2
            );

            $table->decimal(
                'penalty_amount',
                12,
                2
            )->default(0);

            $table->timestamps();
        });


        /*
        |--------------------------------------------------------------------------
        | BAGAMBAKAMO EVENTS
        |--------------------------------------------------------------------------
        */

        Schema::create('bagambakamo_events', function (Blueprint $table) {
            $table->id();

            /*
             * Mwanachama aliyepata tukio.
             */
            $table->foreignId('member_id')
                ->constrained('bagambakamo_members')
                ->cascadeOnDelete();

            /*
             * Example:
             * msiba
             * sherehe
             */
            $table->string('type');

            $table->decimal(
                'amount',
                12,
                2
            );

            $table->decimal(
                'contribution_per_member',
                12,
                2
            )->default(10000);

            $table->date('event_date');

            $table->timestamps();
        });


        /*
        |--------------------------------------------------------------------------
        | BAGAMBAKAMO SMS REPORTS
        |--------------------------------------------------------------------------
        */

        Schema::create('bagambakamo_sms_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('member_id')
                ->nullable()
                ->constrained('bagambakamo_members')
                ->nullOnDelete();

            $table->string('name')->nullable();

            $table->string('phone');

            $table->text('message');

            $table->integer('group_type')->nullable();

            $table->string('status')
                ->default('sent');

            $table->timestamp('sent_at')
                ->nullable();

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        /*
         * Drop in reverse order because of foreign keys.
         */

        Schema::dropIfExists(
            'bagambakamo_sms_reports'
        );

        Schema::dropIfExists(
            'bagambakamo_events'
        );

        Schema::dropIfExists(
            'bagambakamo_group_settings'
        );

        Schema::dropIfExists(
            'bagambakamo_member_balances'
        );

        Schema::dropIfExists(
            'bagambakamo_payments'
        );

        Schema::dropIfExists(
            'bagambakamo_contributions'
        );

        Schema::dropIfExists(
            'bagambakamo_contribution_types'
        );

        Schema::dropIfExists(
            'bagambakamo_members'
        );
    }
};