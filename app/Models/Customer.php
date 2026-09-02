<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'customer_number', 'name', 'phone', 'email', 'address',
        'credit_limit', 'is_active', 'created_by',
    ];

    protected function casts(): array
    {
        return ['credit_limit' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function debts()
    {
        return $this->hasMany(Debt::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
