<?php

namespace App\Jobs;

use App\Models\HotspotManualSmsMessage;
use App\Services\HotspotCustomerBroadcastSmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendHotspotManualSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [30, 120, 300];

    public function __construct(public int $messageId)
    {
    }

    public function handle(HotspotCustomerBroadcastSmsService $sms): void
    {
        $record = HotspotManualSmsMessage::find($this->messageId);

        if (! $record || $record->status === 'sent') {
            return;
        }

        $record->update([
            'status' => 'processing',
            'attempts' => (int) $record->attempts + 1,
            'error' => null,
        ]);

        try {
            $response = $sms->send($record->normalized_phone, $record->message);
            $record->update([
                'status' => 'sent',
                'sent_at' => now(),
                'failed_at' => null,
                'error' => null,
                'response' => $response,
            ]);
        } catch (Throwable $e) {
            $record->update([
                'status' => 'failed',
                'failed_at' => now(),
                'error' => mb_substr($e->getMessage(), 0, 1000),
            ]);
            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        HotspotManualSmsMessage::whereKey($this->messageId)->update([
            'status' => 'failed',
            'failed_at' => now(),
            'error' => mb_substr($exception->getMessage(), 0, 1000),
        ]);
    }
}
