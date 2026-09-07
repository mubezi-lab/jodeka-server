<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_reorder_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->decimal('minimum_units', 14, 2)->default(0);
            $table->decimal('target_units', 14, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            $table->unique(['business_id', 'product_id']);
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('daily_stock_closings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->restrictOnDelete();
            $table->date('closing_date');
            $table->string('status', 20)->default('submitted');
            $table->text('notes')->nullable();
            $table->integer('recorded_by');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->unique(['business_id', 'closing_date']);
            $table->foreign('recorded_by')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('daily_stock_closing_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_stock_closing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->decimal('opening_units', 14, 2);
            $table->decimal('received_units', 14, 2);
            $table->decimal('available_units', 14, 2);
            $table->decimal('closing_units', 14, 2);
            $table->decimal('sold_units', 14, 2);
            $table->decimal('selling_price', 14, 2);
            $table->decimal('average_cost_per_unit', 14, 4)->default(0);
            $table->decimal('revenue', 16, 2)->default(0);
            $table->decimal('cost_of_goods_sold', 16, 2)->default(0);
            $table->decimal('gross_profit', 16, 2)->default(0);
            $table->decimal('closing_stock_value', 16, 2)->default(0);
            $table->timestamps();
            $table->unique(['daily_stock_closing_id', 'product_id'], 'daily_close_product_unique');
        });

        Schema::table('stock_requests', function (Blueprint $table) {
            $table->string('source', 20)->default('manual')->after('status');
            $table->foreignId('daily_stock_closing_id')->nullable()->after('source')
                ->constrained('daily_stock_closings')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable()->after('daily_stock_closing_id');
            $table->unique('daily_stock_closing_id');
        });
    }

    public function down(): void
    {
        Schema::table('stock_requests', function (Blueprint $table) {
            $table->dropUnique(['daily_stock_closing_id']);
            $table->dropConstrainedForeignId('daily_stock_closing_id');
            $table->dropColumn(['source', 'confirmed_at']);
        });
        Schema::dropIfExists('daily_stock_closing_items');
        Schema::dropIfExists('daily_stock_closings');
        Schema::dropIfExists('inventory_reorder_policies');
    }
};
