<?php

namespace App\Models\Bagambakamo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contribution extends Model
{
    protected $table = 'bagambakamo_contributions';

    protected $fillable = [
        'member_id',
        'contribution_type_id',
        'amount',
        'contribution_month',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(
            ContributionType::class,
            'contribution_type_id'
        );
    }
}