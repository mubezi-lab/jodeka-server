<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Business;

class BusinessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Business::insert([
            ['name' => 'Duka', 'type' => 'retail'],
            ['name' => 'Bar', 'type' => 'bar'],
            ['name' => 'Mgahawa', 'type' => 'restaurant'],
            ['name' => 'Ufugaji', 'type' => 'livestock'],
            ['name' => 'Kilimo', 'type' => 'farming'],
            ['name' => 'Rental', 'type' => 'rental'],
        ]);
    }
}
