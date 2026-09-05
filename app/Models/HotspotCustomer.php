<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HotspotCustomer extends Model
{
    protected $fillable = [
        'name', 'phone', 'normalized_phone', 'first_paid_at', 'last_paid_at',
        'total_payments', 'total_amount', 'active', 'sms_allowed', 'last_sms_at',
    ];

    protected $casts = [
        'first_paid_at' => 'datetime',
        'last_paid_at' => 'datetime',
        'last_sms_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'active' => 'boolean',
        'sms_allowed' => 'boolean',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(HotspotCustomerMessage::class);
    }
}
