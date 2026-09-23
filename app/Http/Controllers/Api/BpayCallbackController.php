<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BpayTransaction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BpayCallbackController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Verify Beem secure token
        |--------------------------------------------------------------------------
        */

        $expectedToken = (string) config('services.bpay.secure_token');
        $receivedToken = (string) $request->header('beem-secure-token');

        if (
            $expectedToken === '' ||
            $receivedToken === '' ||
            ! hash_equals($expectedToken, $receivedToken)
        ) {
            Log::warning('BPAY callback rejected: invalid secure token', [
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Validate official Beem callback payload
        |--------------------------------------------------------------------------
        */

        $validator = Validator::make($request->all(), [
            'amount' => ['required', 'numeric'],
            'referenceNumber' => ['required', 'string'],
            'status' => ['required', 'string', 'in:success,failed'],
            'timestamp' => ['required', 'date'],
            'transactionID' => ['required', 'string'],
            'msisdn' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            Log::warning('BPAY callback rejected: invalid payload', [
                'errors' => $validator->errors()->toArray(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Invalid callback payload',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        /*
        |--------------------------------------------------------------------------
        | 3. Process transaction safely
        |--------------------------------------------------------------------------
        */

        try {
            $result = DB::transaction(function () use ($data, $request) {

                $transaction = BpayTransaction::where(
                    'transaction_id',
                    $data['transactionID']
                )
                    ->lockForUpdate()
                    ->first();

                if (! $transaction) {
                    return [
                        'error' => 'transaction_not_found',
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | Verify reference number
                |--------------------------------------------------------------------------
                */

                if (
                    ! hash_equals(
                        (string) $transaction->reference_number,
                        (string) $data['referenceNumber']
                    )
                ) {
                    return [
                        'error' => 'reference_mismatch',
                        'transaction' => $transaction,
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | Verify amount
                |--------------------------------------------------------------------------
                */

                $storedAmount = number_format(
                    (float) $transaction->amount,
                    2,
                    '.',
                    ''
                );

                $callbackAmount = number_format(
                    (float) $data['amount'],
                    2,
                    '.',
                    ''
                );

                if ($storedAmount !== $callbackAmount) {
                    return [
                        'error' => 'amount_mismatch',
                        'transaction' => $transaction,
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | Duplicate callback protection
                |--------------------------------------------------------------------------
                |
                | If we already processed the same final status, acknowledge it
                | without processing the transaction again.
                |
                */

                if ($transaction->status === $data['status']) {
                    return [
                        'duplicate' => true,
                        'transaction' => $transaction,
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | Update BPAY transaction
                |--------------------------------------------------------------------------
                */

                $transaction->status = $data['status'];
                $transaction->mobile = $data['msisdn'];
                $transaction->beem_timestamp = Carbon::parse(
                    $data['timestamp']
                );
                $transaction->callback_received_at = now();
                $transaction->raw_callback = $request->all();

                if ($data['status'] === 'success') {
                    $transaction->paid_at = now();
                    $transaction->failed_at = null;
                }

                if ($data['status'] === 'failed') {
                    $transaction->failed_at = now();
                    $transaction->paid_at = null;
                }

                $transaction->save();

                return [
                    'duplicate' => false,
                    'transaction' => $transaction,
                ];
            });

            /*
            |--------------------------------------------------------------------------
            | 4. Handle rejected callbacks
            |--------------------------------------------------------------------------
            */

            if (($result['error'] ?? null) === 'transaction_not_found') {
                Log::warning('BPAY callback transaction not found', [
                    'transactionID' => $data['transactionID'],
                    'referenceNumber' => $data['referenceNumber'],
                ]);

                return response()->json([
                    'message' => 'Transaction not found',
                ], 404);
            }

            if (($result['error'] ?? null) === 'reference_mismatch') {
                Log::warning('BPAY callback reference mismatch', [
                    'transactionID' => $data['transactionID'],
                    'referenceNumber' => $data['referenceNumber'],
                ]);

                return response()->json([
                    'message' => 'Reference number mismatch',
                ], 409);
            }

            if (($result['error'] ?? null) === 'amount_mismatch') {
                Log::warning('BPAY callback amount mismatch', [
                    'transactionID' => $data['transactionID'],
                    'referenceNumber' => $data['referenceNumber'],
                    'amount' => $data['amount'],
                ]);

                return response()->json([
                    'message' => 'Amount mismatch',
                ], 409);
            }

            $transaction = $result['transaction'];

            /*
            |--------------------------------------------------------------------------
            | 5. Log successfully processed callback
            |--------------------------------------------------------------------------
            */

            Log::info('BPAY callback processed', [
                'transaction_id' => $transaction->transaction_id,
                'reference_number' => $transaction->reference_number,
                'status' => $transaction->status,
                'duplicate' => $result['duplicate'] ?? false,
            ]);

            /*
            |--------------------------------------------------------------------------
            | 6. Return response required by Beem
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'amount' => (string) $data['amount'],
                'status' => $data['status'] === 'success',
                'referenceNumber' => $data['referenceNumber'],
                'statusMessage' => $data['status'] === 'success'
                    ? 'Payment was successful!'
                    : 'Payment failed.',
                'transactionID' => $data['transactionID'],
            ], 200);

        } catch (\Throwable $e) {
            Log::error('BPAY callback processing error', [
                'message' => $e->getMessage(),
                'transactionID' => $data['transactionID'] ?? null,
            ]);

            return response()->json([
                'message' => 'Callback processing failed',
            ], 500);
        }
    }
}