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
        Schema::create('layer_feed_standards', function (Blueprint $table) {
            $table->id();
            $table->integer('week');
            $table->decimal('min_kg', 5, 2);
            $table->decimal('max_kg', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('layer_feed_standards');
    }
};
