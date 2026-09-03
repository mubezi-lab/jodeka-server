<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotspot_permanent_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('network_router_id');
            $table->string('name');
            $table->string('phone', 30)->nullable();
            $table->string('normalized_phone', 20)->nullable()->unique();
            $table->string('mac_address', 17);
            $table->string('user_type', 20);
            $table->decimal('daily_rate', 12, 2)->default(500);
            $table->unsignedBigInteger('usage_threshold_bytes')->default(1048576);
            $table->decimal('credit_balance', 12, 2)->default(0);
            $table->boolean('enabled')->default(true);
            $table->boolean('is_online')->default(false);
            $table->string('last_ip', 45)->nullable();
            $table->dateTime('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['network_router_id', 'mac_address']);
            $table->foreign('network_router_id', 'hp_user_router_fk')
                ->references('id')->on('network_routers')->cascadeOnDelete();
        });

        Schema::create('hotspot_permanent_daily_usages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hotspot_permanent_user_id');
            $table->date('usage_date');
            $table->dateTime('first_seen_at')->nullable();
            $table->dateTime('last_seen_at')->nullable();
            $table->boolean('is_online')->default(false);
            $table->string('last_ip', 45)->nullable();
            $table->string('last_host_id', 100)->nullable();
            $table->unsignedBigInteger('last_bytes_in')->default(0);
            $table->unsignedBigInteger('last_bytes_out')->default(0);
            $table->unsignedBigInteger('bytes_in')->default(0);
            $table->unsignedBigInteger('bytes_out')->default(0);
            $table->unsignedBigInteger('last_uptime_seconds')->default(0);
            $table->timestamps();

            $table->unique(['hotspot_permanent_user_id', 'usage_date'], 'hp_usage_user_date_unique');
            $table->index(['usage_date', 'is_online']);
            $table->foreign('hotspot_permanent_user_id', 'hp_usage_user_fk')
                ->references('id')->on('hotspot_permanent_users')->cascadeOnDelete();
        });

        Schema::create('hotspot_permanent_charges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hotspot_permanent_user_id');
            $table->unsignedBigInteger('hotspot_permanent_daily_usage_id')->nullable();
            $table->date('charge_date');
            $table->decimal('amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->string('status', 20)->default('unpaid');
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['hotspot_permanent_user_id', 'charge_date'], 'hp_charge_user_date_unique');
            $table->index(['charge_date', 'status']);
            $table->foreign('hotspot_permanent_user_id', 'hp_charge_user_fk')
                ->references('id')->on('hotspot_permanent_users')->cascadeOnDelete();
            $table->foreign('hotspot_permanent_daily_usage_id', 'hp_charge_usage_fk')
                ->references('id')->on('hotspot_permanent_daily_usages')->nullOnDelete();
        });

        Schema::create('hotspot_permanent_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hotspot_permanent_user_id');
            $table->unsignedBigInteger('hotspot_payment_id')->nullable()->unique();
            $table->string('method', 20);
            $table->string('reference', 100)->nullable()->unique();
            $table->decimal('amount', 12, 2);
            $table->decimal('allocated_amount', 12, 2)->default(0);
            $table->decimal('credit_amount', 12, 2)->default(0);
            $table->dateTime('paid_at');
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->timestamps();

            $table->foreign('hotspot_permanent_user_id', 'hp_payment_user_fk')
                ->references('id')->on('hotspot_permanent_users')->cascadeOnDelete();
            $table->foreign('hotspot_payment_id', 'hp_payment_source_fk')
                ->references('id')->on('hotspot_payments')->nullOnDelete();
        });

        Schema::create('hotspot_permanent_reminders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hotspot_permanent_user_id');
            $table->date('reminder_date');
            $table->string('reminder_type', 20);
            $table->text('message');
            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('failed_at')->nullable();
            $table->text('error')->nullable();
            $table->json('response')->nullable();
            $table->timestamps();

            $table->unique(
                ['hotspot_permanent_user_id', 'reminder_date', 'reminder_type'],
                'hp_reminder_user_date_type_unique'
            );
            $table->foreign('hotspot_permanent_user_id', 'hp_reminder_user_fk')
                ->references('id')->on('hotspot_permanent_users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotspot_permanent_reminders');
        Schema::dropIfExists('hotspot_permanent_payments');
        Schema::dropIfExists('hotspot_permanent_charges');
        Schema::dropIfExists('hotspot_permanent_daily_usages');
        Schema::dropIfExists('hotspot_permanent_users');
    }
};
