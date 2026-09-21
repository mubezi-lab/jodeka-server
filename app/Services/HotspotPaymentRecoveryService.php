<?php

namespace App\Services;

use App\Jobs\SendHotspotVoucherSmsJob;
use App\Models\HotspotPayment;
use Illuminate\Support\Facades\Cache;
use Throwable;

class HotspotPaymentRecoveryService
{
    public static function routerReadyCacheKey(int $routerId): string
    {
        return 'hotspot-router-ready:' . $routerId;
    }

    public function __construct(
        private HotspotVoucherGenerator $vouchers,
        private HotspotCustomerService $customers
    ) {
    }

    /**
     * @return array{recovered: bool, already_completed: bool, busy: bool, error: ?string}
     */
    public function recover(HotspotPayment $payment): array
    {
        $lock = Cache::lock('hotspot-payment-recovery:' . $payment->id, 30);

        if (! $lock->get()) {
            return [
                'recovered' => false,
                'already_completed' => false,
                'busy' => true,
                'error' => 'Payment recovery is already running.',
            ];
        }

        try {
            $payment = HotspotPayment::with(['profile.router', 'voucher'])
                ->findOrFail($payment->id);

            if ($payment->voucher_id && $payment->voucher) {
                if ($payment->status !== 'completed') {
                    $payment->update(['status' => 'completed']);
                }

                $this->customers->syncPayment($payment->fresh());

                if (
                    $payment->voucher_sms_kind === 'recovery'
                    && $payment->payer_phone
                    && ! in_array(
                        $payment->voucher_sms_status,
                        ['pending', 'processing', 'sent'],
                        true
                    )
                ) {
                    $payment->update([
                        'voucher_sms_status' => 'pending',
                        'voucher_sms_error' => null,
                    ]);
                    SendHotspotVoucherSmsJob::dispatch($payment->id);
                }

                return [
                    'recovered' => false,
                    'already_completed' => true,
                    'busy' => false,
                    'error' => null,
                ];
            }

            if (! $payment->profile) {
                $error = 'No hotspot profile is linked to this payment.';
                $payment->update([
                    'status' => 'unmatched',
                    'voucher_recovery_error' => $error,
                    'voucher_recovery_last_attempt_at' => now(),
                ]);

                return [
                    'recovered' => false,
                    'already_completed' => false,
                    'busy' => false,
                    'error' => $error,
                ];
            }

            $router = $payment->profile->router;

            if (
                ! $router
                || ! $router->enabled
                || ! Cache::get(self::routerReadyCacheKey($router->id), false)
            ) {
                return [
                    'recovered' => false,
                    'already_completed' => false,
                    'busy' => false,
                    'error' => 'MikroTik connection has not been confirmed by the latest sync.',
                ];
            }

            $payment->update([
                'status' => 'recovering',
                'voucher_recovery_attempts' => (int) $payment->voucher_recovery_attempts + 1,
                'voucher_recovery_last_attempt_at' => now(),
                'voucher_recovery_error' => null,
            ]);

            $comment = 'Recovered SMS payment ' . $payment->reference;
            if ($payment->payer_name) {
                $comment .= ' - ' . $payment->payer_name;
            }

            try {
                $voucher = $this->vouchers->generate($payment->profile, $comment, null);
            } catch (Throwable $e) {
                $payment->update([
                    'status' => 'waiting_for_router',
                    'voucher_recovery_error' => mb_substr($e->getMessage(), 0, 1000),
                ]);
                report($e);

                return [
                    'recovered' => false,
                    'already_completed' => false,
                    'busy' => false,
                    'error' => $e->getMessage(),
                ];
            }

            $payment->update([
                'voucher_id' => $voucher->id,
                'status' => 'completed',
                'voucher_sms_kind' => 'recovery',
                'voucher_sms_status' => 'pending',
                'voucher_sms_error' => null,
                'voucher_recovery_completed_at' => now(),
                'voucher_recovery_error' => null,
            ]);

            $this->customers->syncPayment($payment->fresh());
            SendHotspotVoucherSmsJob::dispatch($payment->id);

            return [
                'recovered' => true,
                'already_completed' => false,
                'busy' => false,
                'error' => null,
            ];
        } finally {
            $lock->release();
        }
    }
}
