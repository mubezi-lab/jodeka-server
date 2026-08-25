<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE hotspot_vouchers
            MODIFY COLUMN status
            ENUM('unused', 'used', 'expired', 'disabled', 'cancelled')
            NOT NULL DEFAULT 'unused'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            UPDATE hotspot_vouchers
            SET status = 'disabled'
            WHERE status = 'cancelled'
        ");

        DB::statement("
            ALTER TABLE hotspot_vouchers
            MODIFY COLUMN status
            ENUM('unused', 'used', 'expired', 'disabled')
            NOT NULL DEFAULT 'unused'
        ");
    }
};