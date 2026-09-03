<?php

namespace App\Services;

use App\Models\HotspotPayment;
use App\Models\HotspotPermanentCharge;
use App\Models\HotspotPermanentPayment;
use App\Models\HotspotPermanentUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HotspotPermanentPaymentService
{
    public function __construct(private HotspotPhoneService $phones)
    {
    }

    public function findDailyCustomer(?string $phone): ?HotspotPermanentUser
    {
        $normalized = $this->phones->normalize($phone);

        if (! $normalized) {
            return null;
        }

        return HotspotPermanentUser::where('normalized_phone', $normalized)
            ->where('user_type', 'daily_customer')
            ->where('enabled', true)
            ->first();
    }

    public function recordSmsPayment(
        HotspotPermanentUser $user,
        HotspotPayment $payment
    ): HotspotPermanentPayment {
        return DB::transaction(function () use ($user, $payment) {
            $existing = HotspotPermanentPayment::where(
                'hotspot_payment_id',
                $payment->id
            )->first();

            if ($existing) {
                return $existing;
            }

            $permanentPayment = HotspotPermanentPayment::create([
                'hotspot_permanent_user_id' => $user->id,
                'hotspot_payment_id' => $payment->id,
                'method' => 'lipa_number',
                'reference' => $payment->reference,
                'amount' => $payment->amount,
                'paid_at' => $payment->paid_at ?? now(),
            ]);

            $this->allocate($user, $permanentPayment);

            $payment->status = 'permanent_completed';
            $payment->hotspot_profile_id = null;
            $payment->voucher_id = null;
            $payment->save();

            $fresh = $permanentPayment->fresh();
            $fresh->wasRecentlyCreated = true;

            return $fresh;
        });
    }

    public function recordOfficePayment(
        HotspotPermanentUser $user,
        float $amount,
        ?int $recordedBy
    ): HotspotPermanentPayment {
        return DB::transaction(function () use ($user, $amount, $recordedBy) {
            $payment = HotspotPermanentPayment::create([
                'hotspot_permanent_user_id' => $user->id,
                'method' => 'office',
                'reference' => 'OFFICE-' . now()->format('YmdHis') . '-'
                    . $user->id . '-' . Str::upper(Str::random(4)),
                'amount' => $amount,
                'paid_at' => now(),
                'recorded_by' => $recordedBy,
            ]);

            $this->allocate($user, $payment);

            return $payment->fresh();
        });
    }

    public function applyAvailableCredit(HotspotPermanentUser $user): void
    {
        if ((float) $user->credit_balance <= 0) {
            return;
        }

        $synthetic = new HotspotPermanentPayment([
            'amount' => $user->credit_balance,
            'allocated_amount' => 0,
            'credit_amount' => 0,
        ]);

        $this->allocateAmount($user, $synthetic, false);
    }

    private function allocate(
        HotspotPermanentUser $user,
        HotspotPermanentPayment $payment
    ): void {
        $this->allocateAmount($user, $payment, true);
    }

    private function allocateAmount(
        HotspotPermanentUser $user,
        HotspotPermanentPayment $payment,
        bool $newMoney
    ): void {
        $user = HotspotPermanentUser::whereKey($user->id)->lockForUpdate()->firstOrFail();
        $remaining = $newMoney
            ? (float) $payment->amount
            : (float) $user->credit_balance;
        $allocated = 0.0;

        $charges = HotspotPermanentCharge::where('hotspot_permanent_user_id', $user->id)
            ->whereIn('status', ['unpaid', 'partial'])
            ->orderBy('charge_date')
            ->lockForUpdate()
            ->get();

        foreach ($charges as $charge) {
            if ($remaining <= 0) {
                break;
            }

            $due = (float) $charge->amount - (float) $charge->paid_amount;
            $applied = min($remaining, $due);
            $charge->paid_amount = (float) $charge->paid_amount + $applied;
            $remaining -= $applied;
            $allocated += $applied;

            if ((float) $charge->paid_amount >= (float) $charge->amount) {
                $charge->status = 'paid';
                $charge->paid_at = now();
            } else {
                $charge->status = 'partial';
            }

            $charge->save();
        }

        if ($newMoney) {
            $user->credit_balance = (float) $user->credit_balance + $remaining;
            $payment->allocated_amount = $allocated;
            $payment->credit_amount = $remaining;
            $payment->save();
        } else {
            $user->credit_balance = $remaining;
        }

        $user->save();
    }
}
