<?php

namespace App\Jobs;

use App\Models\HotspotCustomerInvitation;
use App\Services\HotspotCustomerInvitationSmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendHotspotCustomerInvitationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 120, 300];

    public function __construct(
        public int $invitationId
    ) {
    }

    public function handle(
        HotspotCustomerInvitationSmsService $sms
    ): void {
        $invitation = HotspotCustomerInvitation::find(
            $this->invitationId
        );

        if (! $invitation || $invitation->status === 'sent') {
            return;
        }

        $invitation->update([
            'status' => 'processing',
            'attempts' => (int) $invitation->attempts + 1,
            'error' => null,
        ]);

        try {
            $response = $sms->send($invitation->phone);

            $invitation->update([
                'status' => 'sent',
                'sent_at' => now(),
                'failed_at' => null,
                'error' => null,
                'response' => $response,
            ]);
        } catch (Throwable $e) {
            $invitation->update([
                'status' => 'failed',
                'failed_at' => now(),
                'error' => mb_substr($e->getMessage(), 0, 1000),
            ]);

            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        HotspotCustomerInvitation::whereKey(
            $this->invitationId
        )->update([
            'status' => 'failed',
            'failed_at' => now(),
            'error' => mb_substr(
                $exception->getMessage(),
                0,
                1000
            ),
        ]);
    }
}
