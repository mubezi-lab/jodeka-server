<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();

            $table->foreignId('business_id')
                ->constrained('businesses')
                ->restrictOnDelete();

            $table->date('request_date');
            $table->string('status', 30)->default('pending');
            $table->text('notes')->nullable();

            // Legacy users.id is signed INT.
            $table->integer('requested_by');
            $table->integer('reviewed_by')->nullable();

            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();

            $table->foreign('requested_by', 'stock_requests_requested_by_fk')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();

            $table->foreign('reviewed_by', 'stock_requests_reviewed_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::create('stock_request_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stock_request_id')
                ->constrained('stock_requests')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            $table->decimal('requested_packages', 12, 2);
            $table->decimal('approved_packages', 12, 2)->nullable();
            $table->decimal('units_per_package', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(
                ['stock_request_id', 'product_id'],
                'stock_request_items_request_product_unique'
            );
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();

            $table->foreignId('stock_request_id')
                ->nullable()
                ->constrained('stock_requests')
                ->nullOnDelete();

            $table->foreignId('business_id')
                ->constrained('businesses')
                ->restrictOnDelete();

            $table->string('supplier')->nullable();
            $table->date('order_date');
            $table->string('status', 30)->default('ordered');
            $table->text('notes')->nullable();

            // Legacy users.id is signed INT.
            $table->integer('ordered_by');

            $table->timestamps();

            $table->foreign('ordered_by', 'purchase_orders_ordered_by_fk')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_order_id')
                ->constrained('purchase_orders')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            $table->decimal('ordered_packages', 12, 2);
            $table->decimal('units_per_package', 12, 2);
            $table->decimal('cost_per_package', 14, 2);
            $table->decimal('total_cost', 14, 2);
            $table->timestamps();

            $table->unique(
                ['purchase_order_id', 'product_id'],
                'purchase_order_items_order_product_unique'
            );
        });

        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();

            $table->foreignId('purchase_order_id')
                ->constrained('purchase_orders')
                ->restrictOnDelete();

            $table->date('receipt_date');
            $table->string('status', 30)->default('confirmed');
            $table->text('notes')->nullable();

            // Legacy users.id is signed INT.
            $table->integer('received_by');

            $table->timestamps();

            $table->foreign('received_by', 'goods_receipts_received_by_fk')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
        });

        Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('goods_receipt_id')
                ->constrained('goods_receipts')
                ->cascadeOnDelete();

            $table->foreignId('purchase_order_item_id')
                ->constrained('purchase_order_items')
                ->restrictOnDelete();

            $table->decimal('received_packages', 12, 2);

            // Legacy purchases.id is signed INT.
            $table->integer('purchase_id')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(
                ['goods_receipt_id', 'purchase_order_item_id'],
                'gr_items_receipt_order_unique'
            );

            $table->foreign('purchase_id', 'goods_receipt_items_purchase_fk')
                ->references('id')
                ->on('purchases')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_receipt_items');
        Schema::dropIfExists('goods_receipts');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('stock_request_items');
        Schema::dropIfExists('stock_requests');
    }
};