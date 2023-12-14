<?php

namespace Database\Seeders;

use App\Models\MonthPlan;
use Illuminate\Database\Seeder;

class MonthPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MonthPlan::factory(10)->create();
    }
}
