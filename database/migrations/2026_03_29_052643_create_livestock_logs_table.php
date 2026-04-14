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
        Schema::create('livestock_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('livestock_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // mortality, feed
            $table->integer('quantity')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->date('date');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('livestock_logs');
    }
};
