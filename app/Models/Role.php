<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{

    //Relationship with user
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Add role name to fillable

    public function run(): void
    {
        Role::insert([
            ['name' => 'admin'],
            ['name' => 'manager'],
            ['name' => 'employee'],
        ]);
    }
}
