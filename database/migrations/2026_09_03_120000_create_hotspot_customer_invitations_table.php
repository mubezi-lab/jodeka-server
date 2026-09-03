<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotspot_customer_invitations', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 30);
            $table->date('campaign_date');
            $table->text('message');
            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('failed_at')->nullable();
            $table->text('error')->nullable();
            $table->json('response')->nullable();
            $table->timestamps();

            $table->unique(['phone', 'campaign_date']);
            $table->index(['campaign_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotspot_customer_invitations');
    }
};
