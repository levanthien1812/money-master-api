<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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

        User::factory(10)->create()->each(function ($user) {
            $user->assignRole('user');
        });
    }
}
