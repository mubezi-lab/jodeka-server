<?php

namespace App\Jobs;

use App\Services\BeemSmsService;
use App\Models\Bagambakamo\SmsReport;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $member;
    public $group;

    public function __construct($member, $group)
    {
        $this->member = $member;
        $this->group = $group;
    }

    public function handle(BeemSmsService $sms): void
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDATE MEMBER
        |--------------------------------------------------------------------------
        */

        if (
            !$this->member
            ||
            !$this->member->phone
        ) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | MEMBER DETAILS
        |--------------------------------------------------------------------------
        */

        $name = strtoupper(
            $this->member->full_name
            ?? 'Mwanachama'
        );

        $balance = number_format(
            $this->member->balance_amount
            ?? 0
        );

        $month = now()->month;


        /*
        |--------------------------------------------------------------------------
        | MESSAGE BY GROUP
        |--------------------------------------------------------------------------
        */

        if ($this->group == 1) {

            $message =
                "Habari Ndugu {$name}, "
                . "Unakumbushwa kulipa ada yako ya mwezi wa {$month} "
                . "ya TZS {$balance} kabla ya mwisho wa mwezi. "
                . "Asante.";

        } elseif ($this->group == 2) {

            $message =
                "Habari Ndugu {$name}, "
                . "Bado unayo nafasi ya kurudi kwenye kundi la Bagambakamo. "
                . "Tafadhali lipia deni lako la TZS {$balance}. "
                . "Asante.";

        } else {

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | SEND SMS
        |--------------------------------------------------------------------------
        */

        $sms->send(
            [
                [
                    'recipient_id' => $this->member->id,
                    'dest_addr' => $this->member->phone,
                ]
            ],
            $message
        );


        /*
        |--------------------------------------------------------------------------
        | SAVE SMS REPORT
        |--------------------------------------------------------------------------
        */

        SmsReport::create([
            'member_id' => $this->member->id,
            'name' => $this->member->full_name,
            'phone' => $this->member->phone,
            'message' => $message,
            'group_type' => $this->group,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }
}