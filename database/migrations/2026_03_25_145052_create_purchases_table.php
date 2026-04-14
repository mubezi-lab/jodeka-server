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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            // Core data
            $table->decimal('quantity', 10, 2); // inaweza kuwa pcs, kg, trays
            $table->decimal('unit_cost', 10, 2); // bei ya kununua kwa unit

            // Calculated (optional but useful for reports)
            $table->decimal('total_cost', 12, 2)->nullable();

            // Extra info
            $table->date('date');
            $table->string('supplier')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};