<?php

namespace App\Models\Bagambakamo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContributionType extends Model
{
    protected $table = 'bagambakamo_contribution_types';

    protected $fillable = [
        'name',
        'description',
    ];

    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }
}