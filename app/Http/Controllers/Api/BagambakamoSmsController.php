<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bagambakamo\Member;
use App\Models\Bagambakamo\Payment;
use App\Models\Bagambakamo\PendingTransaction;
use App\Services\BagambakamoSmsParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class BagambakamoSmsController extends Controller
{
    public function store(
        Request $request,
        BagambakamoSmsParser $parser
    ): JsonResponse {

        $configuredKey = (string) config(
            'services.hotspot_sms.api_key'
        );

        $providedKey = (string) $request->header(
            'X-JODEKA-SMS-KEY'
        );

        if (
            $configuredKey === ''
            ||
            $providedKey === ''
            ||
            ! hash_equals(
                $configuredKey,
                $providedKey
            )
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        $validated = $request->validate([
            'sender' => [
                'nullable',
                'string',
                'max:100',
            ],
            'sms' => [
                'required',
                'string',
            ],
            'received_at' => [
                'nullable',
                'string',
            ],
        ]);

        $sender = trim(
            $validated['sender'] ?? ''
        );

        if (
            $sender !== ''
            &&
            strcasecmp(
                $sender,
                'M-Koba'
            ) !== 0
        ) {
            return response()->json([
                'success' => false,
                'message' => 'SMS sender is not M-Koba.',
            ], 422);
        }

        $sms = trim(
            $validated['sms']
        );

        if (
            stripos(
                $sms,
                'BAGAMBA KAMO'
            ) === false
        ) {
            return response()->json([
                'success' => false,
                'message' => 'SMS does not belong to BAGAMBA KAMO.',
            ], 422);
        }

        try {

            $data = $parser->parse(
                $sms
            );

            if (
                ($data['direction'] ?? null)
                ===
                'in'
            ) {
                return $this->processContribution(
                    $data,
                    $sms
                );
            }

            if (
                ($data['direction'] ?? null)
                ===
                'out'
            ) {
                return $this->processOutgoing(
                    $data,
                    $sms
                );
            }

            return response()->json([
                'success' => false,
                'message' => 'Unsupported BAGAMBA KAMO transaction.',
            ], 422);

        } catch (InvalidArgumentException $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);

        } catch (Throwable $e) {

            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process Bagambakamo SMS.',
            ], 500);
        }
    }


    private function processContribution(
        array $data,
        string $sms
    ): JsonResponse {

        $member = null;
        $matchedBy = null;

        if (
            ($data['format'] ?? null)
            ===
            'owner_contribution'
        ) {

            $forwarderMemberId = config(
                'services.bagambakamo.forwarder_member_id'
            );

            if ($forwarderMemberId) {

                $member = Member::find(
                    $forwarderMemberId
                );

                if ($member) {
                    $matchedBy = 'forwarder_member';
                }
            }

        } else {

            $smsName = $this->normalizeName(
                $data['member_name'] ?? null
            );

            if ($smsName !== '') {

                $nameMatches = Member::query()
                    ->get()
                    ->filter(
                        function ($candidate) use ($smsName) {
                            return $this->normalizeName(
                                $candidate->full_name
                            ) === $smsName;
                        }
                    )
                    ->values();

                if (
                    $nameMatches->count()
                    ===
                    1
                ) {
                    $member = $nameMatches->first();
                    $matchedBy = 'name';

                } elseif (
                    $nameMatches->count()
                    >
                    1
                ) {

                    $smsPhone = $this->normalizePhone(
                        $data['member_phone'] ?? null
                    );

                    if ($smsPhone !== '') {

                        $member = $nameMatches
                            ->first(
                                function ($candidate) use ($smsPhone) {
                                    return $this->normalizePhone(
                                        $candidate->phone
                                    ) === $smsPhone;
                                }
                            );

                        if ($member) {
                            $matchedBy = 'name_and_phone';
                        }
                    }
                }
            }

            if (
                ! $member
                &&
                ! empty(
                    $data['member_phone']
                )
            ) {

                $smsPhone = $this->normalizePhone(
                    $data['member_phone']
                );

                if ($smsPhone !== '') {

                    $member = Member::query()
                        ->get()
                        ->first(
                            function ($candidate) use ($smsPhone) {
                                return $this->normalizePhone(
                                    $candidate->phone
                                ) === $smsPhone;
                            }
                        );

                    if ($member) {
                        $matchedBy = 'phone';
                    }
                }
            }
        }

        if (! $member) {
            return response()->json([
                'success' => false,
                'message' => 'Contribution received but member could not be matched.',
                'member_name' => $data['member_name'] ?? null,
                'member_phone' => $data['member_phone'] ?? null,
                'amount' => $data['amount'] ?? null,
                'format' => $data['format'] ?? null,
            ], 422);
        }

        $reference = $data['reference']
            ?? null;

        if (! $reference) {

            $reference =
                'MK-'
                .
                strtoupper(
                    substr(
                        hash(
                            'sha256',
                            $sms
                        ),
                        0,
                        20
                    )
                );
        }

        $existingPayment = Payment::where(
            'reference',
            $reference
        )->first();

        if ($existingPayment) {
            return response()->json([
                'success' => true,
                'duplicate' => true,
                'message' => 'Payment already processed.',
                'payment_id' => $existingPayment->id,
                'reference' => $existingPayment->reference,
            ]);
        }

        $payment = DB::transaction(
            function () use (
                $member,
                $data,
                $reference
            ) {

                return Payment::create([
                    'member_id' =>
                        $member->id,

                    'amount' =>
                        $data['amount'],

                    'type' =>
                        'monthly',

                    'description' =>
                        'M-Koba contribution',

                    'payment_date' =>
                        $data['paid_at']
                            ->format('Y-m-d'),

                    'method' =>
                        'M-Koba',

                    'reference' =>
                        $reference,
                ]);
            }
        );

        return response()->json([
            'success' => true,
            'duplicate' => false,
            'transaction_direction' => 'in',
            'message' => 'Bagambakamo payment recorded successfully.',
            'payment_id' => $payment->id,
            'member_id' => $member->id,
            'member_name' => $member->full_name,
            'matched_by' => $matchedBy,
            'amount' => (float) $payment->amount,
            'payment_date' => $data['paid_at']->format('Y-m-d'),
            'method' => $payment->method,
            'reference' => $payment->reference,
        ], 201);
    }


    private function processOutgoing(
        array $data,
        string $sms
    ): JsonResponse {

        /*
        |--------------------------------------------------------------------------
        | DUPLICATE BY M-KOBA REFERENCE
        |--------------------------------------------------------------------------
        */

        $reference = $data['reference'] ?? null;

        if (! $reference) {
            return response()->json([
                'success' => false,
                'message' => 'Outgoing transaction reference is missing.',
            ], 422);
        }

        $existingTransaction = PendingTransaction::where(
            'reference',
            $reference
        )->first();

        if ($existingTransaction) {

            return response()->json([
                'success' => true,
                'duplicate' => true,
                'transaction_direction' => 'out',
                'message' => 'Outgoing transaction already received.',
                'pending_transaction_id' => $existingTransaction->id,
                'reference' => $existingTransaction->reference,
                'status' => $existingTransaction->status,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | RESOLVE BENEFICIARY
        |--------------------------------------------------------------------------
        */

        $member = null;
        $matchedBy = null;

        $recipientName =
            $data['recipient_name']
            ?? null;

        $recipientPhone =
            $data['recipient_phone']
            ?? null;


        /*
        |--------------------------------------------------------------------------
        | "YOU HAVE RECEIVED"
        |--------------------------------------------------------------------------
        */

        if (
            ($data['format'] ?? null)
            ===
            'owner_transfer_out'
        ) {

            $forwarderMemberId = config(
                'services.bagambakamo.forwarder_member_id'
            );

            if ($forwarderMemberId) {

                $member = Member::find(
                    $forwarderMemberId
                );

                if ($member) {

                    $matchedBy = 'forwarder_member';

                    $recipientName =
                        $member->full_name;

                    $recipientPhone =
                        $member->phone;
                }
            }

        } else {

            /*
            |--------------------------------------------------------------------------
            | NAME FIRST
            |--------------------------------------------------------------------------
            */

            $normalizedRecipientName =
                $this->normalizeName(
                    $recipientName
                );

            if (
                $normalizedRecipientName
                !==
                ''
            ) {

                $nameMatches = Member::query()
                    ->get()
                    ->filter(
                        function ($candidate) use (
                            $normalizedRecipientName
                        ) {

                            return $this->normalizeName(
                                $candidate->full_name
                            ) === $normalizedRecipientName;
                        }
                    )
                    ->values();

                if (
                    $nameMatches->count()
                    ===
                    1
                ) {

                    $member =
                        $nameMatches->first();

                    $matchedBy =
                        'name';
                }

                elseif (
                    $nameMatches->count()
                    >
                    1
                ) {

                    $normalizedRecipientPhone =
                        $this->normalizePhone(
                            $recipientPhone
                        );

                    if (
                        $normalizedRecipientPhone
                        !==
                        ''
                    ) {

                        $member = $nameMatches
                            ->first(
                                function ($candidate) use (
                                    $normalizedRecipientPhone
                                ) {

                                    return $this->normalizePhone(
                                        $candidate->phone
                                    ) === $normalizedRecipientPhone;
                                }
                            );

                        if ($member) {
                            $matchedBy = 'name_and_phone';
                        }
                    }
                }
            }


            /*
            |--------------------------------------------------------------------------
            | PHONE FALLBACK
            |--------------------------------------------------------------------------
            */

            if (
                ! $member
                &&
                $recipientPhone
            ) {

                $normalizedRecipientPhone =
                    $this->normalizePhone(
                        $recipientPhone
                    );

                if (
                    $normalizedRecipientPhone
                    !==
                    ''
                ) {

                    $member = Member::query()
                        ->get()
                        ->first(
                            function ($candidate) use (
                                $normalizedRecipientPhone
                            ) {

                                return $this->normalizePhone(
                                    $candidate->phone
                                ) === $normalizedRecipientPhone;
                            }
                        );

                    if ($member) {
                        $matchedBy = 'phone';
                    }
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | BENEFICIARY MUST BE BAGAMBAKAMO MEMBER
        |--------------------------------------------------------------------------
        */

        if (! $member) {

            return response()->json([
                'success' => false,
                'message' => 'Outgoing transaction received but beneficiary could not be matched to a Bagambakamo member.',
                'recipient_name' => $recipientName,
                'recipient_phone' => $recipientPhone,
                'amount' => $data['amount'] ?? null,
                'reference' => $reference,
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | USE MEMBER DATABASE DETAILS
        |--------------------------------------------------------------------------
        */

        $recipientName =
            $member->full_name;

        if (! $recipientPhone) {
            $recipientPhone =
                $member->phone;
        }


        /*
        |--------------------------------------------------------------------------
        | CREATE PENDING TRANSACTION
        |--------------------------------------------------------------------------
        */

        $pendingTransaction = PendingTransaction::create([

            'member_id' =>
                $member->id,

            'recipient_name' =>
                $recipientName,

            'recipient_phone' =>
                $recipientPhone,

            'reference' =>
                $reference,

            'amount' =>
                $data['amount'],

            'transaction_date' =>
                $data['paid_at'],

            'account_balance' =>
                $data['account_balance']
                ?? null,

            'raw_sms' =>
                $sms,

            'status' =>
                'pending',

            'classification' =>
                null,

            'processed_record_id' =>
                null,

            'processed_at' =>
                null,
        ]);


        return response()->json([
            'success' => true,
            'duplicate' => false,
            'transaction_direction' => 'out',
            'message' => 'Outgoing transaction saved and is awaiting admin confirmation.',
            'pending_transaction_id' => $pendingTransaction->id,
            'member_id' => $member->id,
            'beneficiary' => $member->full_name,
            'matched_by' => $matchedBy,
            'amount' => (float) $pendingTransaction->amount,
            'transaction_date' => $pendingTransaction
                ->transaction_date
                ->format('Y-m-d H:i:s'),
            'reference' => $pendingTransaction->reference,
            'account_balance' => $pendingTransaction->account_balance,
            'status' => $pendingTransaction->status,
        ], 201);
    }


    private function normalizeName(
        ?string $name
    ): string {

        if (! $name) {
            return '';
        }

        return Str::of($name)
            ->lower()
            ->replaceMatches(
                '/[^a-z0-9\s]/',
                ''
            )
            ->replaceMatches(
                '/\s+/',
                ' '
            )
            ->trim()
            ->toString();
    }


    private function normalizePhone(
        ?string $phone
    ): string {

        if (! $phone) {
            return '';
        }

        $phone = preg_replace(
            '/\D+/',
            '',
            $phone
        );

        if (! $phone) {
            return '';
        }

        if (
            strlen($phone) === 10
            &&
            str_starts_with(
                $phone,
                '0'
            )
        ) {

            return '255'
                .
                substr(
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

            return '255'
                .
                $phone;
        }

        return $phone;
    }
}