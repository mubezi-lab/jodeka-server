<?php

namespace Tests;

use App\Models\Role;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('roles')) {
            return;
        }

        foreach (['admin', 'manager', 'employee'] as $role) {
            if (! Role::query()->where('name', $role)->exists()) {
                Role::query()->forceCreate([
                    'name' => $role,
                ]);
            }
        }
    }
}