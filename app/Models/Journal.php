<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    protected $fillable = [
        'business_id',
        'journal_number',
        'journal_date',
        'source_type',
        'source_id',
        'description',
        'status',
        'created_by',
        'posted_by',
        'posted_at',
    ];

    protected function casts(): array
    {
        return [
            'journal_date' => 'date',
            'posted_at' => 'datetime',
        ];
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function entries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function poster()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }
}
