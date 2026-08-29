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

        /*
        |--------------------------------------------------------------------------
        | NORMALIZE PHONE
        |--------------------------------------------------------------------------
        */

        $phone = $this->normalizePhone(
            $validated['payer_phone']
        );

        if (! $phone) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Namba ya simu uliyoingiza si sahihi.',
            ], 422);
        }

        try {
            return DB::transaction(
                function () use (
                    $validated,
                    $phone
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | FIND NEWEST UNCLAIMED COMPLETED PAYMENT
                    |--------------------------------------------------------------------------
                    */

                    $payment =
                        HotspotPayment::with([
                            'profile',
                            'voucher',
                        ])
                            ->where(
                                'payer_phone',
                                $phone
                            )
                            ->where(
                                'status',
                                'completed'
                            )
                            ->whereNotNull(
                                'voucher_id'
                            )
                            ->whereNull(
                                'claimed_at'
                            )
                            ->orderByDesc(
                                'paid_at'
                            )
                            ->orderByDesc(
                                'id'
                            )
                            ->lockForUpdate()
                            ->first();

                    /*
                    |--------------------------------------------------------------------------
                    | IF NO UNCLAIMED PAYMENT, CHECK SAME DEVICE'S PREVIOUS PAYMENT
                    |--------------------------------------------------------------------------
                    |
                    | This allows the customer to enter the same phone number again
                    | when reconnecting with the same device.
                    |
                    */

                    if (
                        ! $payment
                        &&
                        ! empty(
                            $validated['mac']
                        )
                    ) {
                        $payment =
                            HotspotPayment::with([
                                'profile',
                                'voucher',
                            ])
                                ->where(
                                    'payer_phone',
                                    $phone
                                )
                                ->where(
                                    'status',
                                    'completed'
                                )
                                ->whereNotNull(
                                    'voucher_id'
                                )
                                ->where(
                                    'claimed_by_mac',
                                    $validated['mac']
                                )
                                ->orderByDesc(
                                    'paid_at'
                                )
                                ->orderByDesc(
                                    'id'
                                )
                                ->first();
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | PAYMENT NOT FOUND
                    |--------------------------------------------------------------------------
                    */

                    if (! $payment) {
                        return response()->json([
                            'success' => false,

                            'message' =>
                                'Malipo hayajapatikana kwa namba hii. '
                                . 'Hakikisha umetumia namba ileile iliyofanya malipo '
                                . 'au subiri sekunde chache kisha ujaribu tena.',
                        ], 404);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | VOUCHER MUST EXIST
                    |--------------------------------------------------------------------------
                    */

                    if (
                        ! $payment->voucher_id
                        ||
                        ! $payment->voucher
                    ) {
                        return response()->json([
                            'success' => false,

                            'message' =>
                                'Malipo yamepatikana lakini voucher bado haijawa tayari. '
                                . 'Jaribu tena baada ya muda mfupi.',
                        ], 409);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | ALREADY CLAIMED
                    |--------------------------------------------------------------------------
                    */

                    if ($payment->claimed_at) {

                        if (
                            $payment->claimed_by_mac
                            &&
                            ! empty(
                                $validated['mac']
                            )
                            &&
                            strcasecmp(
                                $payment->claimed_by_mac,
                                $validated['mac']
                            ) !== 0
                        ) {
                            return response()->json([
                                'success' => false,

                                'message' =>
                                    'Malipo haya tayari yametumika kwenye kifaa kingine.',
                            ], 409);
                        }

                        return response()->json([
                            'success' => true,

                            'message' =>
                                'Malipo tayari yamethibitishwa.',

                            'already_claimed' =>
                                true,

                            'payment_id' =>
                                $payment->id,

                            'payer_phone' =>
                                $payment->payer_phone,

                            'amount' =>
                                $payment->amount,

                            'reference' =>
                                $payment->reference,

                            'hotspot_profile' =>
                                $payment
                                    ->profile
                                    ?->name,

                            'voucher' =>
                                $payment
                                    ->voucher
                                    ->username,
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | CLAIM PAYMENT
                    |--------------------------------------------------------------------------
                    */

                    $payment->claimed_at =
                        now();

                    $payment->claimed_by_mac =
                        $validated['mac']
                        ?? null;

                    $payment->claimed_by_ip =
                        $validated['ip']
                        ?? null;

                    $payment->save();

                    return response()->json([
                        'success' => true,

                        'message' =>
                            'Malipo yamethibitishwa kikamilifu.',

                        'already_claimed' =>
                            false,

                        'payment_id' =>
                            $payment->id,

                        'payer_phone' =>
                            $payment->payer_phone,

                        'amount' =>
                            $payment->amount,

                        'reference' =>
                            $payment->reference,

                        'hotspot_profile' =>
                            $payment
                                ->profile
                                ?->name,

                        'voucher' =>
                            $payment
                                ->voucher
                                ->username,
                    ]);
                }
            );

        } catch (ValidationException $e) {
            throw $e;

        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,

                'message' =>
                    'Imeshindikana kuthibitisha malipo.',
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | NORMALIZE TANZANIA PHONE NUMBER
    |--------------------------------------------------------------------------
    |
    | 0659840000     -> 255659840000
    | 659840000      -> 255659840000
    | +255659840000  -> 255659840000
    |
    */

    private function normalizePhone(
        string $phone
    ): ?string {
        $phone =
            preg_replace(
                '/\D+/',
                '',
                $phone
            );

        if (! $phone) {
            return null;
        }

        if (
            str_starts_with(
                $phone,
                '255'
            )
            &&
            strlen($phone) === 12
        ) {
            return $phone;
        }

        if (
            str_starts_with(
                $phone,
                '0'
            )
            &&
            strlen($phone) === 10
        ) {
            return
                '255'
                . substr(
                    $phone,
                    1
                );
        }

        if (
            strlen($phone) === 9
            &&
            (
                str_starts_with(
                    $phone,
                    '6'
                )
                ||
                str_starts_with(
                    $phone,
                    '7'
                )
            )
        ) {
            return
                '255'
                . $phone;
        }

        return null;
    }
}