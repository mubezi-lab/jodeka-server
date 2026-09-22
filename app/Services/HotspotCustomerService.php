<?php

namespace App\Services;

use App\Models\HotspotCustomer;
use App\Models\HotspotPayment;
use Illuminate\Support\Facades\DB;

class HotspotCustomerService
{
    public function __construct(private HotspotPhoneService $phones)
    {
    }

    public function syncPayment(HotspotPayment $payment): ?HotspotCustomer
    {
        if (! $payment->voucher_id || ! $payment->payer_phone) {
            return null;
        }

        $phone = $this->phones->normalize($payment->payer_phone);

        if (! $phone) {
            return null;
        }

        return DB::transaction(function () use ($payment, $phone) {
            $lockedPayment = HotspotPayment::lockForUpdate()->find($payment->id);

            if (! $lockedPayment) {
                return null;
            }

            if ($lockedPayment->hotspot_customer_id) {
                $customer = $lockedPayment->customer;

                if ($customer && ! $customer->active) {
                    $customer->update([
                        'active' => true,
                        'archived_at' => null,
                        'archive_reason' => null,
                        'active_override_until' => null,
                    ]);
                }

                return $customer;
            }

            $customer = HotspotCustomer::where('normalized_phone', $phone)
                ->lockForUpdate()
                ->first();

            if (! $customer) {
                $customer = HotspotCustomer::create([
                    'name' => $lockedPayment->payer_name,
                    'payment_name' => $lockedPayment->payer_name,
                    'name_source' => 'payment',
                    'phone' => $lockedPayment->payer_phone,
                    'normalized_phone' => $phone,
                    'first_paid_at' => $lockedPayment->paid_at,
                    'last_paid_at' => $lockedPayment->paid_at,
                    'total_payments' => 0,
                    'total_amount' => 0,
                    'active' => true,
                    'sms_allowed' => true,
                ]);
            }

            $customer->phone = $lockedPayment->payer_phone;

            if ($lockedPayment->payer_name) {
                $customer->payment_name = $lockedPayment->payer_name;

                if (! $customer->name || $customer->name_source === 'payment') {
                    $customer->name = $lockedPayment->payer_name;
                    $customer->name_source = 'payment';
                }
            }
            $customer->active = true;
            $customer->archived_at = null;
            $customer->archive_reason = null;
            $customer->active_override_until = null;
            $customer->first_paid_at = $customer->first_paid_at ?: $lockedPayment->paid_at;

            if (! $customer->last_paid_at || $lockedPayment->paid_at?->gt($customer->last_paid_at)) {
                $customer->last_paid_at = $lockedPayment->paid_at;
            }

            $customer->total_payments = (int) $customer->total_payments + 1;
            $customer->total_amount = (float) $customer->total_amount + (float) $lockedPayment->amount;
            $customer->save();

            $lockedPayment->hotspot_customer_id = $customer->id;
            $lockedPayment->save();

            return $customer;
        });
    }
}
