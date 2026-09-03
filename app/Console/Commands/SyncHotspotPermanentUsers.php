<?php

namespace App\Console\Commands;

use App\Services\HotspotPermanentUsageService;
use Illuminate\Console\Command;

class SyncHotspotPermanentUsers extends Command
{
    protected $signature = 'hotspot:permanent-sync';

    protected $description = 'Sync bypassed permanent hotspot users from MikroTik hosts';

    public function handle(HotspotPermanentUsageService $service): int
    {
        $result = $service->syncAll();

        $this->info(
            'Permanent sync completed. Routers: ' . $result['routers']
            . ', Online: ' . $result['online']
            . ', Charges created: ' . $result['charges_created']
            . ', Errors: ' . $result['errors']
        );

        return $result['errors'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
