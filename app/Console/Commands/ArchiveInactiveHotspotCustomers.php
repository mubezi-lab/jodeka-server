<?php

namespace App\Console\Commands;

use App\Models\HotspotCustomer;
use Illuminate\Console\Command;

class ArchiveInactiveHotspotCustomers extends Command
{
    protected $signature = 'hotspot:archive-inactive-customers {--days=3}';

    protected $description = 'Archive hotspot customers without a recent payment';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $cutoff = now('Africa/Dar_es_Salaam')->subDays($days)->utc();

        $count = HotspotCustomer::query()
            ->where('active', true)
            ->where(function ($query) {
                $query->whereNull('active_override_until')
                    ->orWhere('active_override_until', '<=', now());
            })
            ->where(function ($query) use ($cutoff) {
                $query->where('last_paid_at', '<', $cutoff)
                    ->orWhere(function ($query) use ($cutoff) {
                        $query->whereNull('last_paid_at')->where('created_at', '<', $cutoff);
                    });
            })
            ->update([
                'active' => false,
                'archived_at' => now(),
                'archive_reason' => 'inactive_3_days',
                'active_override_until' => null,
            ]);

        $this->info('Archived hotspot customers: ' . $count . '.');

        return self::SUCCESS;
    }
}
