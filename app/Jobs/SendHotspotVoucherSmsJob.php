<?php

namespace App\Jobs;

use App\Models\HotspotPayment;
use App\Services\HotspotVoucherSmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendHotspotVoucherSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 120, 300];

    public function __construct(
        public int $paymentId
    ) {
    }

    public function handle(
        HotspotVoucherSmsService $sms
    ): void {
        $payment = HotspotPayment::with([
            'voucher',
            'profile',
        ])->find($this->paymentId);

        if (
            ! $payment
            || $payment->voucher_sms_status === 'sent'
            || ! $payment->voucher
            || ! $payment->profile
            || ! $payment->payer_phone
        ) {
            return;
        }

        $payment->voucher_sms_status = 'processing';
        $payment->voucher_sms_attempts =
            (int) $payment->voucher_sms_attempts + 1;
        $payment->voucher_sms_error = null;
        $payment->save();

        try {
            $response = $sms->send(
                $payment,
                $payment->voucher,
                $payment->profile
            );

            $payment->voucher_sms_status = 'sent';
            $payment->voucher_sms_sent_at = now();
            $payment->voucher_sms_failed_at = null;
            $payment->voucher_sms_error = null;
            $payment->voucher_sms_response = $response;
            $payment->save();
        } catch (Throwable $e) {
            $payment->voucher_sms_status = 'failed';
            $payment->voucher_sms_failed_at = now();
            $payment->voucher_sms_error = mb_substr(
                $e->getMessage(),
                0,
                1000
            );
            $payment->save();

            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        HotspotPayment::whereKey($this->paymentId)->update([
            'voucher_sms_status' => 'failed',
            'voucher_sms_failed_at' => now(),
            'voucher_sms_error' => mb_substr(
                $exception->getMessage(),
                0,
                1000
            ),
        ]);
    }
}
