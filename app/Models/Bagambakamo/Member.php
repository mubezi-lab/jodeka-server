<?php

namespace App\Models\Bagambakamo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Member extends Model
{
    protected $table = 'bagambakamo_members';

    protected $fillable = [
        'full_name',
        'phone',
        'status',
        'join_date',
    ];

    protected $casts = [
        'join_date' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Phone Normalization
    |--------------------------------------------------------------------------
    |
    | All Tanzanian phone numbers are stored in international format.
    |
    | Examples:
    |
    | 0760855157     -> 255760855157
    | 760855157      -> 255760855157
    | +255760855157  -> 255760855157
    | 255760855157   -> 255760855157
    |
    */

    public function setPhoneAttribute($value): void
    {
        if ($value === null || trim((string) $value) === '') {
            $this->attributes['phone'] = null;

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Remove formatting
        |--------------------------------------------------------------------------
        |
        | Remove spaces, +, -, brackets and any other non-numeric characters.
        |
        */

        $phone = preg_replace(
            '/\D+/',
            '',
            (string) $value
        );

        /*
        |--------------------------------------------------------------------------
        | Local Tanzania Format
        |--------------------------------------------------------------------------
        |
        | 0760855157 -> 255760855157
        | 0659840000 -> 255659840000
        |
        */

        if (
            strlen($phone) === 10
            && str_starts_with($phone, '0')
        ) {
            $phone = '255' . substr($phone, 1);
        }

        /*
        |--------------------------------------------------------------------------
        | Tanzania Number Without Leading Zero
        |--------------------------------------------------------------------------
        |
        | 760855157 -> 255760855157
        | 659840000 -> 255659840000
        |
        */

        elseif (
            strlen($phone) === 9
            && (
                str_starts_with($phone, '7')
                || str_starts_with($phone, '6')
            )
        ) {
            $phone = '255' . $phone;
        }

        /*
        |--------------------------------------------------------------------------
        | Save Normalized Number
        |--------------------------------------------------------------------------
        */

        $this->attributes['phone'] = $phone;
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }

    public function balance(): HasOne
    {
        return $this->hasOne(MemberBalance::class);
    }

    public function smsReports(): HasMany
    {
        return $this->hasMany(SmsReport::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors / Existing Bagambakamo Business Logic
    |--------------------------------------------------------------------------
    */

    public function getTotalPaidAttribute()
    {
        return $this->payments()->sum('amount');
    }

    public function getExpectedAmountAttribute()
    {
        $entry = 180000;

        $months = now()->month;

        $monthly = $months * 10000;

        return $entry + $monthly;
    }

    public function getTotalEventsAttribute()
    {
        return Event::where(
            'member_id',
            '!=',
            $this->id
        )->sum('contribution_per_member');
    }

    public function getBalanceAmountAttribute()
    {
        $balance = (
            $this->expected_amount +
            $this->total_events
        ) - $this->total_paid;

        return $balance > 0
            ? $balance
            : 0;
    }
}