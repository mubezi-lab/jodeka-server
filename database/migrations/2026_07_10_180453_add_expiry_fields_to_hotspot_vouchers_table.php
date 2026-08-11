<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotspot_vouchers', function (Blueprint $table) {
            $table->timestamp('sold_at')
                ->nullable()
                ->after('generated_at');

            $table->timestamp('first_login_at')
                ->nullable()
                ->after('used_at');

            $table->timestamp('expires_at')
                ->nullable()
                ->after('first_login_at');

            $table->timestamp('last_seen_at')
                ->nullable()
                ->after('expires_at');

            $table->string('used_by_mac')
                ->nullable()
                ->after('last_seen_at');

            $table->string('used_by_ip')
                ->nullable()
                ->after('used_by_mac');

            $table->timestamp('disabled_at')
                ->nullable()
                ->after('used_by_ip');
        });
    }

    public function down(): void
    {
        Schema::table('hotspot_vouchers', function (Blueprint $table) {
            $table->dropColumn([
                'sold_at',
                'first_login_at',
                'expires_at',
                'last_seen_at',
                'used_by_mac',
                'used_by_ip',
                'disabled_at',
            ]);
        });
    }
};