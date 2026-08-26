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
        /*
        |--------------------------------------------------------------------------
        | SQLite
        |--------------------------------------------------------------------------
        |
        | Automated tests use SQLite in-memory.
        | SQLite does not support MySQL's MODIFY COLUMN ... ENUM syntax.
        |
        | The original hotspot_vouchers table is still usable in SQLite,
        | so no schema modification is required during tests.
        |
        */

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | MySQL / MariaDB
        |--------------------------------------------------------------------------
        |
        | Add "cancelled" as an allowed hotspot voucher status.
        |
        */

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
        /*
        |--------------------------------------------------------------------------
        | SQLite
        |--------------------------------------------------------------------------
        */

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Convert cancelled vouchers before removing ENUM value
        |--------------------------------------------------------------------------
        */

        DB::statement("
            UPDATE hotspot_vouchers
            SET status = 'disabled'
            WHERE status = 'cancelled'
        ");

        /*
        |--------------------------------------------------------------------------
        | Restore original ENUM
        |--------------------------------------------------------------------------
        */

        DB::statement("
            ALTER TABLE hotspot_vouchers
            MODIFY COLUMN status
            ENUM('unused', 'used', 'expired', 'disabled')
            NOT NULL DEFAULT 'unused'
        ");
    }
};