<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Business;
use Illuminate\Database\Eloquent\Builder;

trait AuthorizesBusinessAccess
{
    protected function accessibleBusinesses(): Builder
    {
        $user = request()->user();

        if ($user->role?->name === 'admin') {
            return Business::query();
        }

        $ids = $user->businesses()
            ->wherePivot('is_active', true)
            ->pluck('businesses.id')
            ->push($user->business_id)
            ->filter()
            ->unique();

        return Business::query()->whereIn('id', $ids);
    }

    protected function authorizeBusiness(int $businessId): void
    {
        abort_unless(
            $this->accessibleBusinesses()->whereKey($businessId)->exists(),
            403,
            'Huna ruhusa ya kutumia branch hii.'
        );
    }
}
