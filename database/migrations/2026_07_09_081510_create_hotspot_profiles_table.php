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
        Schema::create('hotspot_profiles', function (Blueprint $table) {

            $table->id();

            $table->foreignId('network_router_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');

            $table->string('mikrotik_profile');

            $table->decimal('price', 12, 2);

            $table->integer('validity_hours');

            $table->boolean('enabled')->default(true);

            $table->text('description')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotspot_profiles');
    }
};
