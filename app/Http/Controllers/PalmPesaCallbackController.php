<?php

namespace App\Http\Controllers;

use App\Models\HotspotPayment;
use App\Models\PalmPesaCallbackEvent;
use App\Models\PalmPesaTransaction;
use App\Services\HotspotPaymentRecoveryService;
use App\Services\PalmPesaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class PalmPesaCallbackController extends Controller
{
    public function __invoke(
        Request $request,
        PalmPesaService $palmPesa,
        HotspotPaymentRecoveryService $recovery
    ): JsonResponse {
        $validated = $request->validate([
            'event' => [
                'required',
                'string',
                'max:60',
            ],

            'event_id' => [
                'required',
                'string',
                'max:191',
            ],

            'transaction_id' => [
                'required',
                'string',
                'max:191',
            ],

            'order_id' => [
                'required',
                'string',
                'max:191',
            ],

            'status' => [
                'required',
                'string',
                'max:40',
            ],

            'amount' => [
                'required',
                'numeric',
            ],

            'currency' => [
                'required',
                'string',
                'max:3',
            ],

            'selcom_reference' => [
                'nullable',
                'string',
                'max:100',
            ],

            'channel' => [
                'nullable',
                'string',
                'max:50',
            ],

            'failure_reason' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Find JODEKA Palm Pesa transaction
        |--------------------------------------------------------------------------
        */

        $transaction = PalmPesaTransaction::query()
            ->where(
                'transaction_id',
                $validated['transaction_id']
            )
            ->where(
                'order_id',
                $validated['order_id']
            )
            ->first();

        if (! $transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Palm Pesa transaction was not found.',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Store callback event
        |--------------------------------------------------------------------------
        |
        | event_id is unique. Palm Pesa may deliver the same callback more than
        | once, so an already processed event must not generate another voucher.
        |
        */

        $event = PalmPesaCallbackEvent::firstOrCreate(
            [
                'event_id' => $validated['event_id'],
            ],
            [
                'palmpesa_transaction_id' => $transaction->id,
                'event' => $validated['event'],
                'status' => strtoupper($validated['status']),
                'payload' => $request->all(),
                'received_at' => now(),
            ]
        );

        if (
            ! $event->wasRecentlyCreated
            && $event->processed_at
        ) {
            return response()->json([
                'success' => true,
                'message' => 'Callback already processed.',
                'duplicate' => true,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Verify directly with Palm Pesa
        |--------------------------------------------------------------------------
        |
        | We do not trust the callback alone for service fulfilment.
        |
        */

        try {
            $statusResponse = $palmPesa->checkStatus(
                $transaction->order_id
            );

            $statusData = $statusResponse['data'] ?? null;

            if (! is_array($statusData)) {
                throw new RuntimeException(
                    'Palm Pesa status response is missing payment data.'
                );
            }

            $this->verifyTransactionDetails(
                $transaction,
                $statusData
            );

        } catch (Throwable $e) {
            report($e);

            /*
            | Do NOT mark event as processed.
            | Palm Pesa can retry the callback.
            */

            return response()->json([
                'success' => false,
                'message' => 'Unable to verify payment with Palm Pesa.',
            ], 503);
        }

        $verifiedStatus = strtoupper(
            (string) ($statusData['status'] ?? '')
        );

        /*
        |--------------------------------------------------------------------------
        | Update transaction using VERIFIED Palm Pesa response
        |--------------------------------------------------------------------------
        */

        $transaction->update([
            'status' => $verifiedStatus,
            'reference' =>
                $statusData['reference']
                ?? $statusData['selcom_reference']
                ?? $validated['selcom_reference']
                ?? $transaction->reference,

            'channel' =>
                $statusData['channel']
                ?? $validated['channel']
                ?? $transaction->channel,

            'failure_reason' =>
                $statusData['failure_reason']
                ?? $validated['failure_reason']
                ?? null,

            'response_payload' => $statusResponse,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Non-completed final payment
        |--------------------------------------------------------------------------
        |
        | CANCELLED / REJECTED / INITIATION_FAILED / EXPIRED etc.
        | must NEVER create a hotspot payment or voucher.
        |
        */

        if ($verifiedStatus !== 'COMPLETED') {
            $event->update([
                'status' => $verifiedStatus,
                'processed_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Palm Pesa callback processed.',
                'payment_completed' => false,
                'status' => $verifiedStatus,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | COMPLETED - create/link HotspotPayment safely
        |--------------------------------------------------------------------------
        */

        try {
            $payment = DB::transaction(function () use (
                $transaction,
                $event,
                $statusData,
                $statusResponse,
                $request
            ) {
                $lockedTransaction =
                    PalmPesaTransaction::query()
                        ->whereKey($transaction->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                /*
                |--------------------------------------------------------------------------
                | Already linked
                |--------------------------------------------------------------------------
                */

                if ($lockedTransaction->hotspot_payment_id) {
                    $existingPayment =
                        HotspotPayment::find(
                            $lockedTransaction->hotspot_payment_id
                        );

                    if ($existingPayment) {
                        $event->update([
                            'status' => 'COMPLETED',
                            'processed_at' => now(),
                        ]);

                        return $existingPayment;
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Stable unique HotspotPayment reference
                |--------------------------------------------------------------------------
                |
                | We use our own transaction_id so even if Palm Pesa/Selcom
                | reference is absent, hotspot_payments.reference remains unique.
                |
                */

                $hotspotReference =
                    'PP-' . $lockedTransaction->transaction_id;

                $payment = HotspotPayment::firstOrCreate(
                    [
                        'reference' => $hotspotReference,
                    ],
                    [
                        'provider' => 'PALMPESA',
                        'amount' => $lockedTransaction->amount,
                        'hotspot_profile_id' =>
                            $lockedTransaction->hotspot_profile_id,

                        'payer_phone' =>
                            $lockedTransaction->payer_phone,

                        'payer_name' =>
                            $lockedTransaction->payer_name,

                        'paid_at' => now(),

                        'raw_sms' => json_encode(
                            [
                                'source' => 'palmpesa',
                                'callback' => $request->all(),
                                'verification' => $statusResponse,
                            ],
                            JSON_UNESCAPED_UNICODE
                            | JSON_UNESCAPED_SLASHES
                        ),

                        /*
                        | Existing hotspot recovery system will
                        | generate the voucher from this payment.
                        */
                        'status' => 'pending',
                    ]
                );

                $lockedTransaction->update([
                    'hotspot_payment_id' => $payment->id,
                    'status' => 'COMPLETED',

                    'reference' =>
                        $statusData['reference']
                        ?? $statusData['selcom_reference']
                        ?? $lockedTransaction->reference,

                    'channel' =>
                        $statusData['channel']
                        ?? $lockedTransaction->channel,

                    'failure_reason' => null,

                    'response_payload' => $statusResponse,

                    'completed_at' =>
                        $lockedTransaction->completed_at
                        ?? now(),
                ]);

                /*
                | Payment is now safely stored.
                | The callback itself has been successfully handled.
                */

                $event->update([
                    'palmpesa_transaction_id' =>
                        $lockedTransaction->id,

                    'status' => 'COMPLETED',
                    'processed_at' => now(),
                ]);

                return $payment;
            });

        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Payment verified but could not be recorded.',
            ], 500);
        }

        /*
        |--------------------------------------------------------------------------
        | Generate/recover voucher
        |--------------------------------------------------------------------------
        |
        | This happens OUTSIDE the DB transaction because MikroTik communication
        | may take time. HotspotPaymentRecoveryService already provides the
        | voucher-generation recovery/idempotency behaviour used by JODEKA.
        |
        */

        try {
            $result = $recovery->recover($payment);

            return response()->json([
                'success' => true,
                'message' => 'Palm Pesa payment completed and processed.',
                'payment_completed' => true,
                'payment_id' => $payment->id,
                'voucher_ready' =>
                    (bool) (
                        ($result['recovered'] ?? false)
                        || ($result['already_completed'] ?? false)
                    ),
            ]);

        } catch (Throwable $e) {
            /*
            | Payment has already been safely stored.
            | Voucher recovery can retry later.
            */

            report($e);

            return response()->json([
                'success' => true,
                'message' =>
                    'Payment completed and saved. Voucher will be retried.',
                'payment_completed' => true,
                'payment_id' => $payment->id,
                'voucher_ready' => false,
            ]);
        }
    }

    private function verifyTransactionDetails(
        PalmPesaTransaction $transaction,
        array $data
    ): void {
        $verifiedTransactionId =
            (string) ($data['transaction_id'] ?? '');

        $verifiedOrderId =
            (string) ($data['order_id'] ?? '');

        $verifiedCurrency =
            strtoupper(
                (string) ($data['currency'] ?? '')
            );

        $verifiedAmount =
            (int) ($data['amount'] ?? -1);

        if (
            ! hash_equals(
                $transaction->transaction_id,
                $verifiedTransactionId
            )
        ) {
            throw new RuntimeException(
                'Palm Pesa transaction ID mismatch.'
            );
        }

        if (
            ! hash_equals(
                (string) $transaction->order_id,
                $verifiedOrderId
            )
        ) {
            throw new RuntimeException(
                'Palm Pesa order ID mismatch.'
            );
        }

        if ($verifiedCurrency !== 'TZS') {
            throw new RuntimeException(
                'Palm Pesa currency mismatch.'
            );
        }

        if (
            $verifiedAmount !==
            (int) round((float) $transaction->amount)
        ) {
            throw new RuntimeException(
                'Palm Pesa amount mismatch.'
            );
        }
    }
}