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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();

            // Mwanzo wa siku
            $table->integer('opening_stock')->default(0);

            // Zilizonunuliwa
            $table->integer('purchased')->default(0);

            // Jumla (auto - unaweza pia kucompute)
            $table->integer('total_stock')->default(0);

            // Baki (ulichohesabu)
            $table->integer('closing_stock')->default(0);

            // Zilizouzwa
            $table->integer('sold')->default(0);

            // Mauzo
            $table->decimal('sales_amount', 12, 2)->default(0);

            // Faida
            $table->decimal('profit', 12, 2)->default(0);

            $table->date('date');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
