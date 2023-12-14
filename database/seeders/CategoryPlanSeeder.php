<?php

namespace Database\Seeders;

use App\Models\CategoryPlan;
use Illuminate\Database\Seeder;

class CategoryPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CategoryPlan::factory(20)->create();
    }
}
