<?php

namespace App\Console\Commands;

use App\Services\HotspotPermanentReminderService;
use Illuminate\Console\Command;

class SendHotspotPermanentEveningReminders extends Command
{
    protected $signature = 'hotspot:permanent-reminders';

    protected $description = 'Queue evening reminders for unpaid permanent hotspot customers';

    public function handle(HotspotPermanentReminderService $service): int
    {
        $queued = $service->queueEveningReminders();
        $this->info('Permanent hotspot reminders queued: ' . $queued);

        return self::SUCCESS;
    }
}
