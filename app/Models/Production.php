<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    protected $fillable = [
        'livestock_id',
        'trays',
        'pieces',
        'date'
    ];

    public function livestock()
    {
        return $this->belongsTo(Livestock::class);
    }
}