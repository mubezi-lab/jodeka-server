<?php

namespace Tests\Unit;

use App\Models\HotspotPermanentDailyUsage;
use App\Models\HotspotVoucher;
use App\Services\HotspotDataValueService;
use PHPUnit\Framework\TestCase;

class HotspotPermanentDataValueTest extends TestCase
{
    public function test_permanent_usage_uses_the_same_mobile_data_value_formula(): void
    {
        $usage = new HotspotPermanentDailyUsage([
            'bytes_in' => 100 * 1048576,
            'bytes_out' => 146 * 1048576,
        ]);
        $voucher = new HotspotVoucher([
            'bytes_in' => 100 * 1048576,
            'bytes_out' => 146 * 1048576,
        ]);

        $this->assertSame(246 * 1048576, $usage->total_usage_bytes);
        $this->assertSame(500, $usage->data_value);
        $this->assertSame($voucher->data_value, $usage->data_value);
        $this->assertSame(500, HotspotDataValueService::calculate(246 * 1048576));
        $this->assertSame(2100, HotspotDataValueService::calculate(1024 * 1048576));
        $this->assertSame(0, HotspotDataValueService::calculate(0));
    }
}
