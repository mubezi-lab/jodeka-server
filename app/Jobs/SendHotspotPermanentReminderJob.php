<?php

namespace App\Jobs;

use App\Models\HotspotPermanentReminder;
use App\Services\BeemSmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendHotspotPermanentReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $reminderId)
    {
    }

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(BeemSmsService $sms): void
    {
        $reminder = HotspotPermanentReminder::with('user')->findOrFail($this->reminderId);

        if ($reminder->status === 'sent') {
            return;
        }

        $today = now('Africa/Dar_es_Salaam')->toDateString();
        $charges = $reminder->user->charges()
            ->whereIn('status', ['unpaid', 'partial'])
            ->when(
                $reminder->reminder_type === 'returning',
                fn ($query) => $query->whereDate('charge_date', '<', $today)
            )
            ->get();

        $outstanding = $charges->sum(
            fn ($charge) => (float) $charge->amount - (float) $charge->paid_amount
        );

        if ($outstanding <= 0) {
            $reminder->update(['status' => 'cancelled']);
            return;
        }

        $reminder->increment('attempts');
        $reminder->update(['status' => 'processing', 'error' => null]);

        try {
            $response = $sms->send(
                [[
                    'recipient_id' => $reminder->id,
                    'dest_addr' => $reminder->user->normalized_phone,
                ]],
                $reminder->message,
                (string) config('services.hotspot_beem.sender')
            );

            $reminder->update([
                'status' => 'sent',
                'sent_at' => now(),
                'failed_at' => null,
                'response' => $response,
            ]);
        } catch (Throwable $e) {
            $reminder->update([
                'status' => 'failed',
                'failed_at' => now(),
                'error' => mb_substr($e->getMessage(), 0, 1000),
            ]);
            throw $e;
        }
    }
}
