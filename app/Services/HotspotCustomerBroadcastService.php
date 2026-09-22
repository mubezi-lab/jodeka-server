<?php

namespace App\Services;

use App\Jobs\SendHotspotCustomerBroadcastJob;
use App\Models\HotspotCustomer;
use App\Models\HotspotCustomerMessage;
use Throwable;

class HotspotCustomerBroadcastService
{
    public function queue(string $type, string $audience, array $customerIds = []): array
    {
        $date = now('Africa/Dar_es_Salaam')->toDateString();
        $message = HotspotCustomerBroadcastSmsService::message($type);
        $result = ['eligible' => 0, 'queued' => 0, 'already_sent_today' => 0];

        HotspotCustomer::query()
            ->where('sms_allowed', true)
            ->whereNotNull('normalized_phone')
            ->when(
                $audience === 'selected',
                fn ($query) => $query->whereKey($customerIds)
            )
            ->when(
                $audience === 'active',
                fn ($query) => $query->where('active', true)
            )
            ->when(
                $audience === 'recent_archived',
                fn ($query) => $query
                    ->where('active', false)
                    ->whereNotNull('last_paid_at')
                    ->where('last_paid_at', '>=', now()->subDays(30))
            )
            ->orderBy('id')
            ->chunkById(100, function ($customers) use ($type, $date, $message, &$result) {
                foreach ($customers as $customer) {
                    $result['eligible']++;

                    if (
                        $customer->last_sms_at
                        && $customer->last_sms_at
                            ->timezone('Africa/Dar_es_Salaam')
                            ->isSameDay(now('Africa/Dar_es_Salaam'))
                    ) {
                        $result['already_sent_today']++;
                        continue;
                    }

                    $record = HotspotCustomerMessage::firstOrCreate([
                        'hotspot_customer_id' => $customer->id,
                        'campaign_date' => $date,
                        'message_type' => $type,
                    ], [
                        'message' => $message,
                        'status' => 'pending',
                    ]);

                    if (! $record->wasRecentlyCreated) {
                        $result['already_sent_today']++;
                        continue;
                    }

                    try {
                        SendHotspotCustomerBroadcastJob::dispatch($record->id);
                        $result['queued']++;
                    } catch (Throwable $e) {
                        $record->update([
                            'status' => 'failed',
                            'failed_at' => now(),
                            'error' => mb_substr($e->getMessage(), 0, 1000),
                        ]);
                        report($e);
                    }
                }
            });

        return $result;
    }
}
