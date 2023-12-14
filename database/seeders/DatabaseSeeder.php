<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Category;
use App\Models\CategoryPlan;
use App\Models\Goal;
use App\Models\GoalAddition;
use App\Models\MonthPlan;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'api']);
        Role::create(['name' => 'user', 'guard_name' => 'api']);

        $admin = User::create([
            'name' => 'Lê Văn Thiện',
            'email' => 'thienlv@gmail.com',
            'password' => '$2y$10$YAWrh56xYQRcpb2f2K.GSey7kgrwppURkw3OnOhC.E32H5JYp3O0y',
        ]);

        $user = User::create([
            'name' => 'Nguyễn Văn A',
            'email' => 'vananguyen@gmail.com',
            'password' => '$2y$10$YAWrh56xYQRcpb2f2K.GSey7kgrwppURkw3OnOhC.E32H5JYp3O0y',
        ]);

        $admin->assignRole('admin');
        $user->assignRole('user');

        User::factory(12)->create()->each(function ($user) {
            $user->assignRole('user');
        });
        Wallet::factory(60)->create();
        Category::factory(70)->create();
        Transaction::factory(10000)->create();
        MonthPlan::factory(100)->create();
        CategoryPlan::factory(300)->create();
        Goal::factory(100)->create();
        GoalAddition::factory(1200)->create();
    }
}
