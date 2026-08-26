<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotspotVoucher extends Model
{
    /*
    |--------------------------------------------------------------------------
    | MASS ASSIGNMENT
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'network_router_id',
        'hotspot_profile_id',

        'username',
        'password',

        'price',
        'status',
        'source',

        'generated_at',
        'sold_at',

        'used_at',
        'first_login_at',
        'expires_at',
        'last_seen_at',

        'used_by_mac',
        'used_by_ip',

        'bytes_in',
        'bytes_out',
        'packets_in',
        'packets_out',

        'mikrotik_uptime',
        'last_synced_at',

        'disabled_at',

        'generated_by',
        'comment',
    ];

    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'price' => 'decimal:2',

        'bytes_in' => 'integer',
        'bytes_out' => 'integer',
        'packets_in' => 'integer',
        'packets_out' => 'integer',

        'generated_at' => 'datetime',
        'sold_at' => 'datetime',

        'used_at' => 'datetime',
        'first_login_at' => 'datetime',
        'expires_at' => 'datetime',
        'last_seen_at' => 'datetime',

        'last_synced_at' => 'datetime',
        'disabled_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | TOTAL INTERNET USAGE
    |--------------------------------------------------------------------------
    |
    | MikroTik stores download and upload separately.
    | Total usage is both values combined.
    |
    */

    public function getTotalUsageBytesAttribute(): int
    {
        return (int) ($this->bytes_in ?? 0)
            + (int) ($this->bytes_out ?? 0);
    }

    /*
    |--------------------------------------------------------------------------
    | EQUIVALENT MOBILE DATA VALUE
    |--------------------------------------------------------------------------
    |
    | Calculates the approximate monetary value of the data consumed
    | based on normal mobile-data bundle prices.
    |
    | Reference bundles:
    |
    | TZS 500   = 246 MB
    | TZS 1,000 = 492 MB
    | TZS 2,000 = 985 MB
    | TZS 2,100 = 1 GB
    | TZS 3,000 = 1.45 GB
    |
    | This DOES NOT charge the complete bundle price.
    |
    | It calculates the proportional monetary value of the amount
    | of data actually consumed.
    |
    */

    public function getDataValueAttribute(): int
    {
        $totalBytes = $this->total_usage_bytes;

        if ($totalBytes <= 0) {
            return 0;
        }

        /*
        |--------------------------------------------------------------------------
        | CONVERT BYTES TO MB
        |--------------------------------------------------------------------------
        */

        $totalMb = $totalBytes / 1048576;

        /*
        |--------------------------------------------------------------------------
        | SELECT MOBILE DATA RATE
        |--------------------------------------------------------------------------
        */

        if ($totalMb <= 246) {

            /*
            | TZS 500 = 246 MB
            */
            $pricePerMb = 500 / 246;

        } elseif ($totalMb <= 492) {

            /*
            | TZS 1,000 = 492 MB
            */
            $pricePerMb = 1000 / 492;

        } elseif ($totalMb <= 985) {

            /*
            | TZS 2,000 = 985 MB
            */
            $pricePerMb = 2000 / 985;

        } elseif ($totalMb <= 1024) {

            /*
            | TZS 2,100 = 1 GB
            */
            $pricePerMb = 2100 / 1024;

        } else {

            /*
            |--------------------------------------------------------------------------
            | LARGE USAGE
            |--------------------------------------------------------------------------
            |
            | TZS 3,000 = 1.45 GB
            |
            | 1.45 GB = 1.45 × 1024 MB
            |
            | Usage greater than this continues using the rate of the
            | largest reference bundle.
            |
            */

            $pricePerMb = 3000 / (1.45 * 1024);
        }

        /*
        |--------------------------------------------------------------------------
        | CALCULATE DATA VALUE
        |--------------------------------------------------------------------------
        */

        return (int) round(
            $totalMb * $pricePerMb
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VALUE DIFFERENCE
    |--------------------------------------------------------------------------
    |
    | Compares:
    |
    | Equivalent Mobile Data Value
    |
    |               VS
    |
    | Hotspot Voucher Price
    |
    | Positive number:
    | Data consumed was worth MORE than customer paid.
    |
    | Negative number:
    | Data consumed was worth LESS than customer paid.
    |
    | Example:
    |
    | Voucher Price = TZS 200
    | Data Value    = TZS 600
    |
    | Difference = +TZS 400
    |
    */

    public function getValueDifferenceAttribute(): int
    {
        $voucherPrice = (int) round(
            (float) ($this->price ?? 0)
        );

        return $this->data_value - $voucherPrice;
    }

    /*
    |--------------------------------------------------------------------------
    | VALUE GAINED
    |--------------------------------------------------------------------------
    |
    | Shows only positive value gained.
    |
    | Example:
    |
    | Voucher Price = TZS 200
    | Data Value    = TZS 600
    |
    | Value Gained = TZS 400
    |
    */

    public function getValueGainedAttribute(): int
    {
        return max(
            0,
            $this->value_difference
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VALUE GAP
    |--------------------------------------------------------------------------
    |
    | Shows the amount by which data consumption value was lower
    | than the voucher price.
    |
    | Example:
    |
    | Voucher Price = TZS 200
    | Data Value    = TZS 136
    |
    | Value Gap = TZS 64
    |
    */

    public function getValueGapAttribute(): int
    {
        return max(
            0,
            -$this->value_difference
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SAVINGS PERCENTAGE
    |--------------------------------------------------------------------------
    |
    | Shows how much cheaper the hotspot voucher was compared
    | with equivalent mobile data.
    |
    | Example:
    |
    | Voucher Price = TZS 200
    | Data Value    = TZS 600
    |
    | Value Gained = 600 - 200
    |              = 400
    |
    | Savings = 400 / 600 × 100
    |         = 66.67%
    |
    | Savings is shown only when Data Value is greater
    | than Voucher Price.
    |
    */

    public function getSavingsPercentageAttribute(): float
    {
        $dataValue = $this->data_value;

        $voucherPrice = (float) ($this->price ?? 0);

        if ($dataValue <= 0) {
            return 0;
        }

        if ($dataValue <= $voucherPrice) {
            return 0;
        }

        return round(
            (
                ($dataValue - $voucherPrice)
                / $dataValue
            ) * 100,
            2
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ROUTER RELATIONSHIP
    |--------------------------------------------------------------------------
    */

    public function router(): BelongsTo
    {
        return $this->belongsTo(
            NetworkRouter::class,
            'network_router_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HOTSPOT PROFILE RELATIONSHIP
    |--------------------------------------------------------------------------
    */

    public function profile(): BelongsTo
    {
        return $this->belongsTo(
            HotspotProfile::class,
            'hotspot_profile_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATED BY USER
    |--------------------------------------------------------------------------
    */

    public function generator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'generated_by'
        );
    }
}