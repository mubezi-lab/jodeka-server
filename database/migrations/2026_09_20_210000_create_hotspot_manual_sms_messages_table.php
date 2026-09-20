<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotspot_manual_sms_messages', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 30);
            $table->string('normalized_phone', 20);
            $table->text('message');
            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('failed_at')->nullable();
            $table->text('error')->nullable();
            $table->json('response')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
            $table->index('normalized_phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotspot_manual_sms_messages');
    }
};
