<?php

namespace App\Services;

use App\Jobs\SendHotspotCustomerBroadcastJob;
use App\Models\HotspotCustomer;
use App\Models\HotspotCustomerMessage;
use Throwable;

class HotspotCustomerBroadcastService
{
    public function queue(string $type): array
    {
        $date = now('Africa/Dar_es_Salaam')->toDateString();
        $message = HotspotCustomerBroadcastSmsService::message($type);
        $result = ['eligible' => 0, 'queued' => 0, 'already_sent_today' => 0];

        HotspotCustomer::where('active', true)
            ->where('sms_allowed', true)
            ->whereNotNull('normalized_phone')
            ->orderBy('id')
            ->chunkById(100, function ($customers) use ($type, $date, $message, &$result) {
                foreach ($customers as $customer) {
                    $result['eligible']++;
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
