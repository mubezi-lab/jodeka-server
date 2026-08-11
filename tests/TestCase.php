<?php

namespace Tests;

use App\Models\Role;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'manager', 'employee'] as $role) {
            if (! Role::query()->where('name', $role)->exists()) {
                Role::query()->forceCreate(['name' => $role]);
            }
        }
    }
}
