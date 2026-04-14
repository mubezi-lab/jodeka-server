<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\LayerFeedStandard;

class Livestock extends Model
{
    protected $fillable = [
        'name',
        'type',
        'category',
        'quantity',
        'start_date',
    ];

    protected $casts = [
        'start_date' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function logs()
    {
        return $this->hasMany(LivestockLog::class);
    }

    public function productions()
    {
        return $this->hasMany(Production::class);
    }

    /*
    |--------------------------------------------------------------------------
    | 🧠 AGE & WEEK (ACCESSORS)
    |--------------------------------------------------------------------------
    */

    public function getAgeInDaysAttribute(): int
    {
        return $this->start_date
            ? $this->start_date->diffInDays(now())
            : 0;
    }

    public function getCurrentWeekAttribute(): int
    {
        $days = $this->age_in_days;

        return $days < 7 ? 1 : (int) floor($days / 7) + 1;
    }

    /*
    |--------------------------------------------------------------------------
    | 🐔 FEED SYSTEM
    |--------------------------------------------------------------------------
    */

    public function getExpectedFeedAttribute(): float
    {
        $standard = LayerFeedStandard::where('week', $this->current_week)->first();

        return $standard
            ? (float) $this->quantity * $standard->max_kg
            : 0;
    }

    public function getQuantityAtDate($date): int
    {
        $mortality = $this->logs()
            ->where('type', 'mortality')
            ->whereDate('date', '<=', $date)
            ->sum('quantity');

        return $this->quantity + $mortality;
    }

    public function getFeedStandardByDate($date)
    {
        if (!$this->start_date) return null;

        $days = Carbon::parse($date)->diffInDays($this->start_date);
        $week = (int) floor($days / 7) + 1;

        return LayerFeedStandard::where('week', $week)->first();
    }

    public function getDailyFeed($date): float
    {
        $qty = $this->getQuantityAtDate($date);
        $standard = $this->getFeedStandardByDate($date);

        return $standard
            ? (float) $qty * $standard->max_kg
            : 0;
    }

    /*
    |--------------------------------------------------------------------------
    | 📊 COST SYSTEM
    |--------------------------------------------------------------------------
    */

    public function totalCost(): float
    {
        return (float) $this->logs()
            ->where('type', 'expense')
            ->sum('amount');
    }

    public function feedCost(): float
    {
        return (float) $this->logs()
            ->where('type', 'expense')
            ->where('category', 'feed')
            ->sum('amount');
    }

    public function chicksCost(): float
    {
        return (float) $this->logs()
            ->where('type', 'expense')
            ->where('category', 'chicks')
            ->sum('amount');
    }

    public function medicineCost(): float
    {
        return (float) $this->logs()
            ->where('type', 'expense')
            ->where('category', 'medicine')
            ->sum('amount');
    }

    public function vaccineCost(): float
    {
        return (float) $this->logs()
            ->where('type', 'expense')
            ->where('category', 'vaccine')
            ->sum('amount');
    }

    /*
    |--------------------------------------------------------------------------
    | ☠️ MORTALITY
    |--------------------------------------------------------------------------
    */

    public function totalMortality(): int
    {
        return (int) $this->logs()
            ->where('type', 'mortality')
            ->sum('quantity');
    }

    /*
    |--------------------------------------------------------------------------
    | 💀 COST OF DEAD (🔥 FIXED LOGIC)
    |--------------------------------------------------------------------------
    */

    public function costPerDead(): float
    {
        $mortalityLogs = $this->logs()
            ->where('type', 'mortality')
            ->orderBy('date')
            ->get();

        if ($mortalityLogs->isEmpty()) return 0;

        $totalDeadCost = 0;
        $initialBirds = $this->quantity + $this->totalMortality();

        foreach ($mortalityLogs as $log) {

            // gharama hadi siku hiyo
            $costUntilThatDay = $this->logs()
                ->where('type', 'expense')
                ->whereDate('date', '<=', $log->date)
                ->sum('amount');

            if ($initialBirds == 0) continue;

            $costPerBird = $costUntilThatDay / $initialBirds;

            // zidisha kwa waliokufa siku hiyo
            $totalDeadCost += $costPerBird * $log->quantity;
        }

        return $totalDeadCost;
    }

    /*
    |--------------------------------------------------------------------------
    | 🐓 COST PER ALIVE (FIXED)
    |--------------------------------------------------------------------------
    */

    public function costPerAlive(): float
    {
        $alive = $this->quantity;

        if ($alive <= 0) return 0;

        $aliveCost = $this->totalCost() - $this->costPerDead();

        return $aliveCost / $alive;
    }

    /*
    |--------------------------------------------------------------------------
    | 🔄 BACKWARD COMPATIBILITY
    |--------------------------------------------------------------------------
    */

    public function totalFeedCost(): float
    {
        return $this->feedCost();
    }
}