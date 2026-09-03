<?php

namespace App\Services;

use App\Jobs\SendHotspotPermanentReminderJob;
use App\Models\HotspotPermanentCharge;
use App\Models\HotspotPermanentReminder;
use App\Models\HotspotPermanentUser;
use Throwable;

class HotspotPermanentReminderService
{
    public function queueEveningReminders(): int
    {
        $today = now('Africa/Dar_es_Salaam')->toDateString();
        $queued = 0;

        $users = HotspotPermanentUser::where('user_type', 'daily_customer')
            ->where('enabled', true)
            ->whereNotNull('normalized_phone')
            ->whereHas('charges', function ($query) use ($today) {
                $query->whereDate('charge_date', $today)
                    ->whereIn('status', ['unpaid', 'partial']);
            })
            ->get();

        foreach ($users as $user) {
            $queued += $this->queue($user, 'evening', false) ? 1 : 0;
        }

        return $queued;
    }

    public function queueArrearsWhenOnline(HotspotPermanentUser $user): bool
    {
        $today = now('Africa/Dar_es_Salaam')->toDateString();
        $hasArrears = $user->charges()
            ->whereDate('charge_date', '<', $today)
            ->whereIn('status', ['unpaid', 'partial'])
            ->exists();

        return $hasArrears && $this->queue($user, 'returning', true);
    }

    private function queue(
        HotspotPermanentUser $user,
        string $type,
        bool $arrearsOnly
    ): bool {
        if (! $user->normalized_phone) {
            return false;
        }

        $today = now('Africa/Dar_es_Salaam')->toDateString();
        $charges = HotspotPermanentCharge::where('hotspot_permanent_user_id', $user->id)
            ->whereIn('status', ['unpaid', 'partial'])
            ->when($arrearsOnly, fn ($query) => $query->whereDate('charge_date', '<', $today))
            ->get();

        $balance = $charges->sum(fn ($charge) => (float) $charge->amount - (float) $charge->paid_amount);

        if ($balance <= 0) {
            return false;
        }

        $days = $charges->count();
        $message = 'JODEKA Hotspot: Una deni la internet la siku '
            . $days
            . ', jumla TZS '
            . number_format($balance, 0)
            . '. Tafadhali fika ofisini ulipie au lipa kupitia Lipa Namba 19361296. Asante.';

        $reminder = HotspotPermanentReminder::firstOrCreate(
            [
                'hotspot_permanent_user_id' => $user->id,
                'reminder_date' => $today,
                'reminder_type' => $type,
            ],
            [
                'message' => $message,
                'status' => 'pending',
            ]
        );

        if (! $reminder->wasRecentlyCreated) {
            return false;
        }

        try {
            SendHotspotPermanentReminderJob::dispatch($reminder->id);
        } catch (Throwable $e) {
            $reminder->update([
                'status' => 'failed',
                'failed_at' => now(),
                'error' => mb_substr($e->getMessage(), 0, 1000),
            ]);
            report($e);
        }

        return true;
    }
}
