<?php

namespace App\Services;

use Carbon\Carbon;
use InvalidArgumentException;

class BagambakamoSmsParser
{
    /*
    |--------------------------------------------------------------------------
    | GENERAL PARSER
    |--------------------------------------------------------------------------
    |
    | Automatically identifies whether the BAGAMBA KAMO SMS is:
    |
    | 1. Member contribution
    | 2. Forwarder-owner contribution
    | 3. Money transferred from group to another person
    | 4. Money received by the forwarder owner from group
    |
    */

    public function parse(string $sms): array
    {
        $sms = trim($sms);

        /*
        |--------------------------------------------------------------------------
        | INCOMING CONTRIBUTIONS
        |--------------------------------------------------------------------------
        */

        try {
            return $this->parseContribution($sms);
        } catch (InvalidArgumentException $e) {
            // Continue to outgoing formats.
        }

        /*
        |--------------------------------------------------------------------------
        | OUTGOING TRANSACTIONS
        |--------------------------------------------------------------------------
        */

        return $this->parseOutgoing($sms);
    }


    /*
    |--------------------------------------------------------------------------
    | CONTRIBUTION PARSER
    |--------------------------------------------------------------------------
    */

    public function parseContribution(string $sms): array
    {
        $sms = trim($sms);


        /*
        |--------------------------------------------------------------------------
        | FORMAT 1 - NORMAL MEMBER CONTRIBUTION
        |--------------------------------------------------------------------------
        |
        | GEORGE KAMUGISHA(255759160734) has contributed TZS.10,000.00
        | to BAGAMBA KAMO group on 28/08/2026 at 11:53
        |
        */

        $memberPattern =
            '/^(.+?)\((\d{10,15})\)\s+has contributed\s+'
            . 'TZS\.?\s*([\d,]+(?:\.\d{2})?)\s+'
            . 'to\s+BAGAMBA KAMO\s+group\s+'
            . 'on\s+(\d{2}\/\d{2}\/\d{4})\s+'
            . 'at\s+(\d{2}:\d{2})/i';


        if (
            preg_match(
                $memberPattern,
                $sms,
                $matches
            )
        ) {

            $name = trim(
                $matches[1]
            );

            $phone = trim(
                $matches[2]
            );

            $amount = $this->parseAmount(
                $matches[3]
            );

            $paidAt = Carbon::createFromFormat(
                'd/m/Y H:i',
                $matches[4]
                . ' '
                . $matches[5]
            );


            return [
                'direction' => 'in',

                'transaction_type' =>
                    'contribution',

                'format' =>
                    'member_contribution',

                'reference' =>
                    null,

                'member_name' =>
                    $name,

                'member_phone' =>
                    $phone,

                'recipient_name' =>
                    null,

                'recipient_phone' =>
                    null,

                'amount' =>
                    $amount,

                'paid_at' =>
                    $paidAt,

                'account_balance' =>
                    null,

                'raw_sms' =>
                    $sms,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | FORMAT 2 - FORWARDER OWNER CONTRIBUTION
        |--------------------------------------------------------------------------
        |
        | DFUDI1KC39 Confirmed.You successfully contributed TZS.10,000.00
        | to BAGAMBA KAMO group on 30/06/2026 at 18:51
        |
        */

        $ownerPattern =
            '/^([A-Z0-9]+)\s+Confirmed\.?\s*'
            . 'You successfully contributed\s+'
            . 'TZS\.?\s*([\d,]+(?:\.\d{2})?)\s+'
            . 'to\s+BAGAMBA KAMO\s+group\s+'
            . 'on\s+(\d{2}\/\d{2}\/\d{4})\s+'
            . 'at\s+(\d{2}:\d{2})/i';


        if (
            preg_match(
                $ownerPattern,
                $sms,
                $matches
            )
        ) {

            $amount = $this->parseAmount(
                $matches[2]
            );

            $paidAt = Carbon::createFromFormat(
                'd/m/Y H:i',
                $matches[3]
                . ' '
                . $matches[4]
            );


            return [
                'direction' => 'in',

                'transaction_type' =>
                    'contribution',

                'format' =>
                    'owner_contribution',

                'reference' =>
                    strtoupper(
                        $matches[1]
                    ),

                'member_name' =>
                    null,

                'member_phone' =>
                    null,

                'recipient_name' =>
                    null,

                'recipient_phone' =>
                    null,

                'amount' =>
                    $amount,

                'paid_at' =>
                    $paidAt,

                'account_balance' =>
                    null,

                'raw_sms' =>
                    $sms,
            ];
        }


        throw new InvalidArgumentException(
            'SMS is not a recognized BAGAMBA KAMO contribution.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | OUTGOING PARSER
    |--------------------------------------------------------------------------
    */

    public function parseOutgoing(string $sms): array
    {
        $sms = trim($sms);


        /*
        |--------------------------------------------------------------------------
        | FORMAT 3 - TRANSFER TO ANOTHER PERSON
        |--------------------------------------------------------------------------
        |
        | DBD4MVYOBS4 Confirmed.TZS.305,000.00 has been transfered
        | from BAGAMBA KAMO account to Mpesa number
        | 255749317767(RICHARD NGAMBEKI).
        | Date 2026-02-13 at 14:46:57.
        | A group account balance is TZS.1,312,000.00
        |
        */

        $transferPattern =
            '/^([A-Z0-9]+)\s+Confirmed\.?\s*'
            . 'TZS\.?\s*([\d,]+(?:\.\d{2})?)\s+'
            . 'has been transfered\s+from\s+'
            . 'BAGAMBA KAMO\s+account\s+to\s+'
            . 'Mpesa\s+number\s+'
            . '(\d{10,15})\(([^)]+)\)\.?\s*'
            . 'Date\s+'
            . '(\d{4}-\d{2}-\d{2})\s+'
            . 'at\s+'
            . '(\d{2}:\d{2}:\d{2})\.?\s*'
            . 'A\s+group\s+account\s+balance\s+is\s+'
            . 'TZS\.?\s*([\d,]+(?:\.\d{2})?)/i';


        if (
            preg_match(
                $transferPattern,
                $sms,
                $matches
            )
        ) {

            $reference = strtoupper(
                $matches[1]
            );

            $amount = $this->parseAmount(
                $matches[2]
            );

            $recipientPhone = trim(
                $matches[3]
            );

            $recipientName = trim(
                $matches[4]
            );

            $paidAt = Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $matches[5]
                . ' '
                . $matches[6]
            );

            $accountBalance = $this->parseAmount(
                $matches[7]
            );


            return [
                'direction' =>
                    'out',

                'transaction_type' =>
                    'transfer',

                'format' =>
                    'member_transfer_out',

                'reference' =>
                    $reference,

                'member_name' =>
                    null,

                'member_phone' =>
                    null,

                'recipient_name' =>
                    $recipientName,

                'recipient_phone' =>
                    $recipientPhone,

                'amount' =>
                    $amount,

                'paid_at' =>
                    $paidAt,

                'account_balance' =>
                    $accountBalance,

                'raw_sms' =>
                    $sms,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | FORMAT 4 - FORWARDER OWNER RECEIVES MONEY
        |--------------------------------------------------------------------------
        |
        | DHN4Q29OTP Confirmed.You have received TZS.600,000.00
        | from BAGAMBA KAMO account.
        | Date 2026-08-23 at 11:13:32.
        | A group account balance is TZS.1,980,000.00
        |
        */

        $ownerReceivedPattern =
            '/^([A-Z0-9]+)\s+Confirmed\.?\s*'
            . 'You have received\s+'
            . 'TZS\.?\s*([\d,]+(?:\.\d{2})?)\s+'
            . 'from\s+BAGAMBA KAMO\s+account\.?\s*'
            . 'Date\s+'
            . '(\d{4}-\d{2}-\d{2})\s+'
            . 'at\s+'
            . '(\d{2}:\d{2}:\d{2})\.?\s*'
            . 'A\s+group\s+account\s+balance\s+is\s+'
            . 'TZS\.?\s*([\d,]+(?:\.\d{2})?)/i';


        if (
            preg_match(
                $ownerReceivedPattern,
                $sms,
                $matches
            )
        ) {

            $reference = strtoupper(
                $matches[1]
            );

            $amount = $this->parseAmount(
                $matches[2]
            );

            $paidAt = Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $matches[3]
                . ' '
                . $matches[4]
            );

            $accountBalance = $this->parseAmount(
                $matches[5]
            );


            return [
                'direction' =>
                    'out',

                'transaction_type' =>
                    'transfer',

                'format' =>
                    'owner_transfer_out',

                'reference' =>
                    $reference,

                'member_name' =>
                    null,

                'member_phone' =>
                    null,

                /*
                |--------------------------------------------------------------------------
                | "YOU" = FORWARDER OWNER
                |--------------------------------------------------------------------------
                |
                | Recipient will later be resolved using:
                |
                | BAGAMBAKAMO_FORWARDER_MEMBER_ID
                |
                */

                'recipient_name' =>
                    null,

                'recipient_phone' =>
                    null,

                'amount' =>
                    $amount,

                'paid_at' =>
                    $paidAt,

                'account_balance' =>
                    $accountBalance,

                'raw_sms' =>
                    $sms,
            ];
        }


        throw new InvalidArgumentException(
            'SMS is not a recognized BAGAMBA KAMO outgoing transaction.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PARSE MONEY
    |--------------------------------------------------------------------------
    */

    private function parseAmount(
        string $amount
    ): float {

        return (float) str_replace(
            ',',
            '',
            trim($amount)
        );
    }
}