<?php

namespace App\Http\Controllers;

use App\Jobs\SendHotspotVoucherSmsJob;
use App\Models\HotspotPayment;
use App\Models\HotspotProfile;
use App\Services\HotspotPaymentSmsParser;
use App\Services\HotspotPermanentPaymentService;
use App\Services\HotspotVoucherGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class HotspotPaymentSmsController extends Controller
{
    public function store(
        Request $request,
        HotspotPaymentSmsParser $parser,
        HotspotVoucherGenerator $voucherGenerator,
        HotspotPermanentPaymentService $permanentPayments
    ): JsonResponse {
        /*
        |--------------------------------------------------------------------------
        | Verify SMS API Key
        |--------------------------------------------------------------------------
        */

        $configuredKey = (string) config('services.hotspot_sms.api_key');
        $providedKey = (string) $request->header('X-JODEKA-SMS-KEY');

        if (
            $configuredKey === '' ||
            $providedKey === '' ||
            ! hash_equals($configuredKey, $providedKey)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Request
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'sms' => ['required', 'string'],
        ]);

        try {
            /*
            |--------------------------------------------------------------------------
            | Parse SMS
            |--------------------------------------------------------------------------
            */

            $data = $parser->parse($validated['sms']);

            /*
            |--------------------------------------------------------------------------
            | Check Existing Payment
            |--------------------------------------------------------------------------
            */

            $payment = HotspotPayment::with([
                'profile',
                'voucher',
            ])
                ->where('reference', $data['reference'])
                ->first();

            /*
            |--------------------------------------------------------------------------
            | Existing Payment Already Has Voucher
            |--------------------------------------------------------------------------
            */

            if ($payment && $payment->voucher_id) {
                $this->dispatchVoucherSmsIfNeeded($payment);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment already processed.',
                    'duplicate' => true,
                    'payment_id' => $payment->id,
                    'reference' => $payment->reference,
                    'status' => $payment->status,
                    'voucher_id' => $payment->voucher_id,
                    'voucher' => $payment->voucher?->username,
                    'hotspot_profile_id' => $payment->hotspot_profile_id,
                    'hotspot_profile' => $payment->profile?->name,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | REGISTERED DAILY CUSTOMER PAYMENT
            |--------------------------------------------------------------------------
            |
            | A payment from an enabled permanent daily customer's registered phone
            | settles that customer's oldest charges. It must not generate a voucher.
            |
            */

            $permanentUser = $permanentPayments->findDailyCustomer(
                $data['payer_phone'] ?? null
            );

            if ($permanentUser) {
                if (! $payment) {
                    $data['hotspot_profile_id'] = null;
                    $data['status'] = 'permanent_pending';
                    $payment = HotspotPayment::create($data);
                }

                $permanentPayment = $permanentPayments->recordSmsPayment(
                    $permanentUser,
                    $payment
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Permanent hotspot customer payment recorded successfully.',
                    'duplicate' => ! $permanentPayment->wasRecentlyCreated,
                    'payment_id' => $payment->id,
                    'permanent_user_id' => $permanentUser->id,
                    'permanent_user' => $permanentUser->name,
                    'amount' => $payment->amount,
                    'allocated_amount' => $permanentPayment->allocated_amount,
                    'credit_amount' => $permanentPayment->credit_amount,
                    'status' => 'permanent_completed',
                ], $payment->wasRecentlyCreated ? 201 : 200);
            }

            /*
            |--------------------------------------------------------------------------
            | Match Amount With Enabled Hotspot Profile
            |--------------------------------------------------------------------------
            */

            $profile = HotspotProfile::where('enabled', true)
                ->where('price', $data['amount'])
                ->orderBy('id')
                ->first();

            /*
            |--------------------------------------------------------------------------
            | Create New Payment
            |--------------------------------------------------------------------------
            */

            if (! $payment) {
                $data['hotspot_profile_id'] = $profile?->id;
                $data['status'] = $profile ? 'pending' : 'unmatched';

                $payment = HotspotPayment::create($data);
            }

            /*
            |--------------------------------------------------------------------------
            | Existing Payment Without Profile - Try Matching Again
            |--------------------------------------------------------------------------
            */

            if (! $payment->hotspot_profile_id && $profile) {
                $payment->hotspot_profile_id = $profile->id;
                $payment->status = 'pending';
                $payment->save();
            }

            /*
            |--------------------------------------------------------------------------
            | No Matching Package
            |--------------------------------------------------------------------------
            */

            if (! $profile && ! $payment->hotspot_profile_id) {
                $payment->status = 'unmatched';
                $payment->save();

                return response()->json([
                    'success' => false,
                    'message' => 'Payment received, but no enabled hotspot package matches this amount.',
                    'payment_saved' => true,
                    'payment_id' => $payment->id,
                    'amount' => $payment->amount,
                    'reference' => $payment->reference,
                    'status' => $payment->status,
                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | Load Matched Profile
            |--------------------------------------------------------------------------
            */

            $profile = HotspotProfile::with('router')
                ->find($payment->hotspot_profile_id);

            if (! $profile) {
                $payment->status = 'unmatched';
                $payment->save();

                return response()->json([
                    'success' => false,
                    'message' => 'Matched hotspot package could not be found.',
                    'payment_saved' => true,
                    'payment_id' => $payment->id,
                    'reference' => $payment->reference,
                    'status' => $payment->status,
                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | Generate Voucher
            |--------------------------------------------------------------------------
            */

            try {
                $comment = 'SMS payment '
                    . $payment->reference;

                if ($payment->payer_name) {
                    $comment .= ' - ' . $payment->payer_name;
                }

                $voucher = $voucherGenerator->generate(
                    $profile,
                    $comment,
                    null
                );

                /*
                |--------------------------------------------------------------------------
                | Link Voucher To Payment
                |--------------------------------------------------------------------------
                */

                $payment->voucher_id = $voucher->id;
                $payment->status = 'completed';
                $payment->voucher_sms_status = 'pending';
                $payment->voucher_sms_error = null;
                $payment->save();

                $this->dispatchVoucherSms($payment);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment received and voucher generated successfully.',
                    'duplicate' => false,
                    'payment_id' => $payment->id,
                    'provider' => $payment->provider,
                    'amount' => $payment->amount,
                    'payer_phone' => $payment->payer_phone,
                    'payer_name' => $payment->payer_name,
                    'reference' => $payment->reference,
                    'status' => $payment->status,
                    'hotspot_profile_id' => $profile->id,
                    'hotspot_profile' => $profile->name,
                    'voucher_id' => $voucher->id,
                    'voucher' => $voucher->username,
                ], 201);

            } catch (Throwable $e) {
                /*
                |--------------------------------------------------------------------------
                | Payment Must Not Be Lost If Voucher Generation Fails
                |--------------------------------------------------------------------------
                */

                $payment->status = 'voucher_failed';
                $payment->save();

                report($e);

                return response()->json([
                    'success' => false,
                    'message' => 'Payment was saved, but voucher generation failed. The payment can be retried.',
                    'payment_saved' => true,
                    'payment_id' => $payment->id,
                    'reference' => $payment->reference,
                    'status' => $payment->status,
                    'hotspot_profile_id' => $profile->id,
                    'hotspot_profile' => $profile->name,
                ], 503);
            }

        } catch (ValidationException $e) {
            throw $e;

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);

        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process SMS.',
            ], 500);
        }
    }

    private function dispatchVoucherSmsIfNeeded(
        HotspotPayment $payment
    ): void {
        if (
            ! $payment->voucher_id
            || ! $payment->payer_phone
            || in_array(
                $payment->voucher_sms_status,
                ['pending', 'processing', 'sent'],
                true
            )
        ) {
            return;
        }

        $this->dispatchVoucherSms($payment);
    }

    private function dispatchVoucherSms(
        HotspotPayment $payment
    ): void {

        $payment->voucher_sms_status = 'pending';
        $payment->voucher_sms_error = null;
        $payment->save();

        try {
            SendHotspotVoucherSmsJob::dispatch($payment->id);
        } catch (Throwable $e) {
            $payment->voucher_sms_status = 'failed';
            $payment->voucher_sms_error = mb_substr(
                $e->getMessage(),
                0,
                1000
            );
            $payment->save();

            report($e);
        }
    }
}
