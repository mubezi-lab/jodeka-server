<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BpayTransaction;
use App\Services\BpayCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class BpayCheckoutController extends Controller
{
    public function store(
        Request $request,
        BpayCheckoutService $bpay
    ): JsonResponse|RedirectResponse {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'mobile' => ['required', 'string', 'regex:/^255\d{9}$/'],
            'email' => ['nullable', 'email'],
            'purpose' => ['nullable', 'string', 'max:100'],
        ]);

        $transactionId = (string) Str::uuid();

        $referenceNumber =
            'MUBEZI-' .
            now()->format('YmdHis') .
            '-' .
            strtoupper(Str::random(6));

        $transaction = BpayTransaction::create([
            'transaction_id' => $transactionId,
            'reference_number' => $referenceNumber,
            'amount' => $validated['amount'],
            'mobile' => $validated['mobile'],
            'email' => $validated['email'] ?? null,
            'purpose' => $validated['purpose'] ?? null,
            'status' => 'pending',
        ]);

        try {
            $response = $bpay->checkout(
                amount: $transaction->amount,
                referenceNumber: $transaction->reference_number,
                mobile: $transaction->mobile,
                email: $transaction->email,
                transactionId: $transaction->transaction_id,
            );

            $data = $response->json();

            $checkoutUrl = is_array($data)
                ? ($data['src'] ?? null)
                : null;

            /*
            |--------------------------------------------------------------------------
            | Checkout request failed
            |--------------------------------------------------------------------------
            */

            if (
                ! $response->successful() ||
                ! $checkoutUrl ||
                str_contains($checkoutUrl, '/error?')
            ) {
                $transaction->update([
                    'status' => 'checkout_error',
                    'metadata' => [
                        'http_status' => $response->status(),
                        'response' => $data ?? $response->body(),
                    ],
                ]);

                Log::warning('BPAY checkout request failed', [
                    'transaction_id' => $transaction->transaction_id,
                    'reference_number' => $transaction->reference_number,
                    'http_status' => $response->status(),
                    'response' => $data ?? $response->body(),
                ]);

                return response()->json([
                    'message' => 'Unable to start BPAY checkout.',
                    'transaction_id' => $transaction->transaction_id,
                    'reference_number' => $transaction->reference_number,
                ], 502);
            }

            /*
            |--------------------------------------------------------------------------
            | Save checkout URL
            |--------------------------------------------------------------------------
            */

            $transaction->update([
                'checkout_url' => $checkoutUrl,
                'metadata' => [
                    'checkout_created_at' => now()->toIso8601String(),
                ],
            ]);

            /*
            |--------------------------------------------------------------------------
            | API / mobile client
            |--------------------------------------------------------------------------
            */

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Checkout created successfully.',
                    'transaction_id' => $transaction->transaction_id,
                    'reference_number' => $transaction->reference_number,
                    'amount' => $transaction->amount,
                    'status' => $transaction->status,
                    'checkout_url' => $checkoutUrl,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Browser
            |--------------------------------------------------------------------------
            */

            return redirect()->away($checkoutUrl);

        } catch (Throwable $e) {
            $transaction->update([
                'status' => 'checkout_error',
                'metadata' => [
                    'error' => $e->getMessage(),
                ],
            ]);

            Log::error('BPAY checkout exception', [
                'transaction_id' => $transaction->transaction_id,
                'reference_number' => $transaction->reference_number,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Unable to start BPAY checkout.',
                'transaction_id' => $transaction->transaction_id,
                'reference_number' => $transaction->reference_number,
            ], 500);
        }
    }
}