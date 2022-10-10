<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'first_name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('123456'),
            'is_admin' => true,
            'is_active' => true,
            'is_public' => true,
            'is_fulltime_hire_ready' => false,
            'is_freelance_hire_ready' => false,
        ]);
    }
}
