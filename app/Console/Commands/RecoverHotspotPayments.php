<?php

namespace App\Console\Commands;

use App\Models\HotspotPayment;
use App\Services\HotspotPaymentRecoveryService;
use Illuminate\Console\Command;

class RecoverHotspotPayments extends Command
{
    protected $signature = 'hotspot:recover-payments {--limit=25}';

    protected $description = 'Retry paid hotspot transactions whose voucher could not be created';

    public function handle(HotspotPaymentRecoveryService $recovery): int
    {
        $payments = HotspotPayment::query()
            ->whereNull('voucher_id')
            ->whereNotNull('hotspot_profile_id')
            ->whereIn('status', ['pending', 'voucher_failed', 'waiting_for_router'])
            ->orderBy('paid_at')
            ->orderBy('id')
            ->limit(max(1, (int) $this->option('limit')))
            ->get();

        $recovered = 0;

        foreach ($payments as $payment) {
            $result = $recovery->recover($payment);

            if ($result['recovered']) {
                $recovered++;
                $this->info('Recovered payment #' . $payment->id . '.');
                continue;
            }

            if ($result['busy'] || $result['already_completed']) {
                continue;
            }

            // One failed connection is enough to establish that the router is
            // still unavailable. Avoid producing one timeout for every payment.
            $this->warn('Recovery paused: ' . ($result['error'] ?: 'router unavailable'));
            break;
        }

        $this->line('Recovered: ' . $recovered . '. Waiting: ' . max(0, $payments->count() - $recovered) . '.');

        return self::SUCCESS;
    }
}
