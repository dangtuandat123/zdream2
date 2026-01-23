<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@ezshot.ai',
            'password' => bcrypt('123456'),
            'is_admin' => true,
            'credits' => 100,
        ]);
        
        $this->command->info('Admin user created: admin@ezshot.ai / 123456');
    }
}
