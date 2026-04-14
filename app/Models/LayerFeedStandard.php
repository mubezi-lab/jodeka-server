<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LayerFeedStandard extends Model
{
    protected $fillable = [
        'week',
        'min_kg',
        'max_kg'
    ];
}
