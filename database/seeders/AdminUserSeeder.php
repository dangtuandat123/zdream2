<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Skip nếu admin đã tồn tại
        if (User::where('email', 'admin@ezshot.ai')->exists()) {
            $this->command->info('Admin user already exists, skipping...');
            return;
        }

        // Generate random secure password
        $password = Str::random(16);

        User::create([
            'name' => 'Admin',
            'email' => 'admin@ezshot.ai',
            'password' => bcrypt($password),
            'is_admin' => true,
            'credits' => 100,
        ]);
        
        $this->command->warn('');
        $this->command->warn('╔══════════════════════════════════════════╗');
        $this->command->warn('║  🔐 ADMIN CREDENTIALS (LƯU LẠI NGAY!)    ║');
        $this->command->warn('╠══════════════════════════════════════════╣');
        $this->command->warn("║  Email: admin@ezshot.ai");
        $this->command->warn("║  Password: {$password}");
        $this->command->warn('╚══════════════════════════════════════════╝');
        $this->command->warn('');
    }
}

