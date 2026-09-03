<?php

namespace App\Services;

use App\Jobs\SendHotspotCustomerInvitationJob;
use App\Models\HotspotCustomerInvitation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class HotspotCustomerInvitationService
{
    public function eligiblePhones(): Collection
    {
        $campaignDate = now('Africa/Dar_es_Salaam')
            ->toDateString();

        return DB::table('hotspot_payments as payments')
            ->join(
                'hotspot_vouchers as vouchers',
                'vouchers.id',
                '=',
                'payments.voucher_id'
            )
            ->whereNotNull('payments.payer_phone')
            ->where('payments.payer_phone', '<>', '')
            ->whereNotNull('vouchers.first_login_at')
            ->where('vouchers.status', 'expired')
            ->whereDate('payments.paid_at', '<', $campaignDate)
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('hotspot_permanent_users as permanent_users')
                    ->whereColumn(
                        'permanent_users.normalized_phone',
                        'payments.payer_phone'
                    )
                    ->where('permanent_users.enabled', true);
            })
            ->whereNotExists(function ($query) use ($campaignDate) {
                $query->selectRaw('1')
                    ->from('hotspot_payments as today_payments')
                    ->whereColumn(
                        'today_payments.payer_phone',
                        'payments.payer_phone'
                    )
                    ->whereDate(
                        'today_payments.paid_at',
                        $campaignDate
                    );
            })
            ->whereNotExists(function ($query) use ($campaignDate) {
                $query->selectRaw('1')
                    ->from('hotspot_customer_invitations as invitations')
                    ->whereColumn(
                        'invitations.phone',
                        'payments.payer_phone'
                    )
                    ->whereDate(
                        'invitations.campaign_date',
                        $campaignDate
                    );
            })
            ->distinct()
            ->orderBy('payments.payer_phone')
            ->pluck('payments.payer_phone');
    }

    public function eligibleCount(): int
    {
        return $this->eligiblePhones()->count();
    }

    public function queueEligible(): int
    {
        $campaignDate = now('Africa/Dar_es_Salaam')
            ->toDateString();
        $message = HotspotCustomerInvitationSmsService::message();
        $queued = 0;

        foreach ($this->eligiblePhones() as $phone) {
            $invitation = HotspotCustomerInvitation::firstOrCreate(
                [
                    'phone' => $phone,
                    'campaign_date' => $campaignDate,
                ],
                [
                    'message' => $message,
                    'status' => 'pending',
                ]
            );

            if (! $invitation->wasRecentlyCreated) {
                continue;
            }

            try {
                SendHotspotCustomerInvitationJob::dispatch(
                    $invitation->id
                );
                $queued++;
            } catch (Throwable $e) {
                $invitation->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'error' => mb_substr($e->getMessage(), 0, 1000),
                ]);

                report($e);
            }
        }

        return $queued;
    }
}
