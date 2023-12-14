<?php

namespace Database\Seeders;

use App\Models\GoalAddition;
use Illuminate\Database\Seeder;

class GoalAdditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        GoalAddition::factory(1200)->create();
    }
}
