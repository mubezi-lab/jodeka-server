<?php

namespace App\Http\Controllers;

use App\Models\HotspotPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class HotspotPaymentVerificationController extends Controller
{
    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payer_phone' => [
                'required',
                'string',
                'max:30',
            ],

            'amount' => [
                'required',
                'numeric',
                'min:1',
            ],

            'mac' => [
                'nullable',
                'string',
                'max:50',
            ],

            'ip' => [
                'nullable',
                'string',
                'max:50',
            ],
        ]);

        $phone = $this->normalizePhone(
            $validated['payer_phone']
        );

        try {
            return DB::transaction(
                function () use (
                    $validated,
                    $phone
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Find Unclaimed Completed Payment
                    |--------------------------------------------------------------------------
                    */

                    $payment = HotspotPayment::with([
                        'profile',
                        'voucher',
                    ])
                        ->where(
                            'payer_phone',
                            $phone
                        )
                        ->where(
                            'amount',
                            $validated['amount']
                        )
                        ->where(
                            'status',
                            'completed'
                        )
                        ->whereNull(
                            'claimed_at'
                        )
                        ->orderByDesc(
                            'paid_at'
                        )
                        ->lockForUpdate()
                        ->first();

                    /*
                    |--------------------------------------------------------------------------
                    | No Unclaimed Payment
                    |--------------------------------------------------------------------------
                    */

                    if (! $payment) {

                        /*
                        |--------------------------------------------------------------------------
                        | Check Whether Same Phone Has A Claimed Payment On Same Device
                        |--------------------------------------------------------------------------
                        */

                        $existing = HotspotPayment::with([
                            'profile',
                            'voucher',
                        ])
                            ->where(
                                'payer_phone',
                                $phone
                            )
                            ->where(
                                'amount',
                                $validated['amount']
                            )
                            ->where(
                                'status',
                                'completed'
                            )
                            ->whereNotNull(
                                'claimed_at'
                            )
                            ->orderByDesc(
                                'claimed_at'
                            )
                            ->first();

                        if (
                            $existing &&
                            $existing->claimed_by_mac &&
                            ! empty($validated['mac']) &&
                            strcasecmp(
                                $existing->claimed_by_mac,
                                $validated['mac']
                            ) === 0
                        ) {
                            return response()->json([
                                'success' => true,
                                'message' => 'Malipo tayari yamethibitishwa kwenye kifaa hiki.',
                                'already_claimed' => true,
                                'payment_id' => $existing->id,
                                'payer_phone' => $existing->payer_phone,
                                'amount' => $existing->amount,
                                'reference' => $existing->reference,
                                'hotspot_profile' => $existing->profile?->name,
                                'voucher' => $existing->voucher?->username,
                            ]);
                        }

                        return response()->json([
                            'success' => false,
                            'message' => 'Malipo hayajapatikana. Hakikisha namba ya simu na kifurushi ni sahihi, au subiri sekunde chache kisha ujaribu tena.',
                        ], 404);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Voucher Check
                    |--------------------------------------------------------------------------
                    */

                    if (
                        ! $payment->voucher_id ||
                        ! $payment->voucher
                    ) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Malipo yamepatikana lakini voucher bado haijapatikana. Tafadhali jaribu tena baada ya muda mfupi.',
                        ], 409);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Claim Payment
                    |--------------------------------------------------------------------------
                    */

                    $payment->claimed_at = now();

                    $payment->claimed_by_mac =
                        $validated['mac'] ?? null;

                    $payment->claimed_by_ip =
                        $validated['ip'] ?? null;

                    $payment->save();

                    return response()->json([
                        'success' => true,
                        'message' => 'Malipo yamethibitishwa kikamilifu.',
                        'already_claimed' => false,

                        'payment_id' =>
                            $payment->id,

                        'payer_phone' =>
                            $payment->payer_phone,

                        'amount' =>
                            $payment->amount,

                        'reference' =>
                            $payment->reference,

                        'hotspot_profile' =>
                            $payment->profile?->name,

                        'voucher' =>
                            $payment->voucher->username,
                    ]);
                }
            );

        } catch (ValidationException $e) {
            throw $e;

        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Imeshindikana kuthibitisha malipo.',
            ], 500);
        }
    }

    private function normalizePhone(
        string $phone
    ): string {
        $phone = preg_replace(
            '/\D+/',
            '',
            $phone
        );

        if (str_starts_with($phone, '255')) {
            return $phone;
        }

        if (
            str_starts_with($phone, '0') &&
            strlen($phone) === 10
        ) {
            return '255' . substr(
                $phone,
                1
            );
        }

        if (strlen($phone) === 9) {
            return '255' . $phone;
        }

        return $phone;
    }
}