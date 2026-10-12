<?php

namespace App\Http\Controllers;

use App\Models\HotspotProfile;
use App\Models\PalmPesaTransaction;
use App\Services\PalmPesaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class PalmPesaHotspotPaymentController extends Controller
{
    public function store(
        Request $request,
        PalmPesaService $palmPesa
    ): JsonResponse {
        $validated = $request->validate([
            'hotspot_profile_id' => [
                'required',
                'integer',
                'exists:hotspot_profiles,id',
            ],

            'payer_phone' => [
                'required',
                'string',
                'max:30',
            ],

            'payer_name' => [
                'required',
                'string',
                'max:255',
            ],

            'payer_email' => [
                'required',
                'email',
                'max:255',
            ],
        ]);

        $phone = $this->normalizePhone(
            $validated['payer_phone']
        );

        if (! $phone) {
            return response()->json([
                'success' => false,
                'message' => 'Namba ya simu si sahihi.',
            ], 422);
        }

        $profile = HotspotProfile::query()
            ->whereKey($validated['hotspot_profile_id'])
            ->where('enabled', true)
            ->first();

        if (! $profile) {
            return response()->json([
                'success' => false,
                'message' => 'Hotspot package haipatikani au imezimwa.',
            ], 422);
        }

        $amount = (int) round((float) $profile->price);

        if ($amount < 100) {
            return response()->json([
                'success' => false,
                'message' => 'Kiasi cha malipo hakikubaliki kwa Palm Pesa.',
            ], 422);
        }

        $callbackUrl = trim(
            (string) config('services.palmpesa.callback_url')
        );

        if (
            $callbackUrl === ''
            || ! str_starts_with($callbackUrl, 'https://')
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Palm Pesa callback URL is not configured correctly.',
            ], 500);
        }

        $transactionId =
            'JDK-HS-' . strtoupper((string) Str::ulid());

        $payload = [
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'currency' => 'TZS',
            'phone' => $phone,
            'name' => trim($validated['payer_name']),
            'email' => strtolower(trim($validated['payer_email'])),
            'callback_url' => $callbackUrl,
            'buyer_remarks' => 'JODEKA Hotspot - ' . $profile->name,
            'merchant_remarks' => 'JODEKA Hotspot',
        ];

        $transaction = PalmPesaTransaction::create([
            'hotspot_profile_id' => $profile->id,
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'currency' => 'TZS',
            'payer_phone' => $phone,
            'payer_name' => trim($validated['payer_name']),
            'payer_email' => strtolower(trim($validated['payer_email'])),
            'status' => 'INITIATING',
            'request_payload' => $payload,
        ]);

        try {
            $response = $palmPesa->requestPayment($payload);

            $data = $response['data'] ?? [];

            $orderId = $data['order_id'] ?? null;

            if (
                ! is_string($orderId)
                || trim($orderId) === ''
            ) {
                throw new RuntimeException(
                    'Palm Pesa response did not include an order_id.'
                );
            }

            $transaction->update([
                'order_id' => $orderId,
                'status' => $data['status'] ?? 'INITIATING',
                'reference' => $data['reference'] ?? null,
                'channel' => $data['channel'] ?? null,
                'failure_reason' => $data['failure_reason'] ?? null,
                'response_payload' => $response,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ombi la malipo limetumwa kwenye simu.',
                'payment_completed' => false,
                'transaction_id' => $transaction->transaction_id,
                'order_id' => $transaction->order_id,
                'status' => $transaction->status,
                'amount' => $transaction->amount,
                'currency' => $transaction->currency,
                'payer_phone' => $transaction->payer_phone,
                'hotspot_profile_id' => $profile->id,
                'hotspot_profile' => $profile->name,
            ], 202);

        } catch (Throwable $e) {
            $transaction->update([
                'status' => 'REQUEST_ERROR',
                'failure_reason' => mb_substr(
                    $e->getMessage(),
                    0,
                    255
                ),
                'response_payload' => [
                    'error' => $e->getMessage(),
                ],
            ]);

            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Imeshindikana kuanzisha malipo ya Palm Pesa.',
                'transaction_id' => $transaction->transaction_id,
            ], 502);
        }
    }

    private function normalizePhone(string $phone): ?string
    {
        $phone = preg_replace('/\D+/', '', $phone);

        if (! $phone) {
            return null;
        }

        if (
            str_starts_with($phone, '255')
            && strlen($phone) === 12
        ) {
            return $phone;
        }

        if (
            str_starts_with($phone, '0')
            && strlen($phone) === 10
        ) {
            return '255' . substr($phone, 1);
        }

        if (
            strlen($phone) === 9
            && (
                str_starts_with($phone, '6')
                || str_starts_with($phone, '7')
            )
        ) {
            return '255' . $phone;
        }

        return null;
    }
}