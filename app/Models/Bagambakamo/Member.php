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