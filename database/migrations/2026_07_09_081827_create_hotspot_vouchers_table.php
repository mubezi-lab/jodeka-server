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
        Schema::create('hotspot_vouchers', function (Blueprint $table) {

            $table->id();

            $table->foreignId('network_router_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('hotspot_profile_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('username')->unique();

            $table->string('password');

            $table->decimal('price', 12, 2);

            $table->enum('status', [
                'unused',
                'used',
                'expired',
                'disabled',
            ])->default('unused');

            $table->timestamp('generated_at')->nullable();

            $table->timestamp('used_at')->nullable();

            $table->unsignedBigInteger('generated_by')->nullable();

            $table->text('comment')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotspot_vouchers');
    }
};
