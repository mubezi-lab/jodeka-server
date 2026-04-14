<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LivestockLog extends Model
{
    protected $fillable = [
        'livestock_id',
        'type',
        'category', 
        'quantity',
        'amount',
        'date',
        'note',
    ];

    public function livestock()
    {
        return $this->belongsTo(Livestock::class);
    }
}
