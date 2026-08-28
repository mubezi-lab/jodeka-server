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
            'reference' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:1'],
            'mac' => ['nullable', 'string', 'max:50'],
            'ip' => ['nullable', 'string', 'max:50'],
        ]);

        try {
            return DB::transaction(function () use ($validated) {

                $payment = HotspotPayment::with([
                    'profile',
                    'voucher',
                ])
                    ->where('reference', $validated['reference'])
                    ->lockForUpdate()
                    ->first();

                if (! $payment) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Malipo hayajapatikana. Hakikisha Kumbukumbu No. ni sahihi au subiri sekunde chache kisha ujaribu tena.',
                    ], 404);
                }

                if ((float) $payment->amount !== (float) $validated['amount']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kiasi ulicholipa hakilingani na kifurushi ulichochagua.',
                        'paid_amount' => $payment->amount,
                    ], 422);
                }

                if ($payment->status !== 'completed') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Malipo yamepatikana lakini voucher bado haijawa tayari. Tafadhali jaribu tena baada ya muda mfupi.',
                        'status' => $payment->status,
                    ], 409);
                }

                if (! $payment->voucher_id || ! $payment->voucher) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Malipo yamethibitishwa lakini voucher haijapatikana.',
                    ], 409);
                }

                /*
                |--------------------------------------------------------------------------
                | Already Claimed
                |--------------------------------------------------------------------------
                */

                if ($payment->claimed_at) {
                    if (
                        $payment->claimed_by_mac &&
                        ! empty($validated['mac']) &&
                        strcasecmp(
                            $payment->claimed_by_mac,
                            $validated['mac']
                        ) !== 0
                    ) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Kumbukumbu hii tayari imetumika kwenye kifaa kingine.',
                        ], 409);
                    }

                    return response()->json([
                        'success' => true,
                        'message' => 'Malipo tayari yamethibitishwa.',
                        'already_claimed' => true,
                        'payment_id' => $payment->id,
                        'reference' => $payment->reference,
                        'amount' => $payment->amount,
                        'hotspot_profile' => $payment->profile?->name,
                        'voucher' => $payment->voucher->username,
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Claim Payment
                |--------------------------------------------------------------------------
                */

                $payment->claimed_at = now();
                $payment->claimed_by_mac = $validated['mac'] ?? null;
                $payment->claimed_by_ip = $validated['ip'] ?? null;
                $payment->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Malipo yamethibitishwa kikamilifu.',
                    'already_claimed' => false,
                    'payment_id' => $payment->id,
                    'reference' => $payment->reference,
                    'amount' => $payment->amount,
                    'hotspot_profile' => $payment->profile?->name,
                    'voucher' => $payment->voucher->username,
                ]);
            });

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
}