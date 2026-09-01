<?php

namespace App\Models\Bagambakamo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    protected $table = 'bagambakamo_events';

    protected $fillable = [
        'member_id',
        'type',
        'amount',
        'contribution_per_member',
        'event_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'contribution_per_member' => 'decimal:2',
        'event_date' => 'date',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}