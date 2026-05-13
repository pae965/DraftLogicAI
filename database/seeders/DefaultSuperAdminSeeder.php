<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultSuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('email', 'admin@rus.ac.th')->exists()) {
            return;
        }

        User::create([
            'name'     => 'Super Admin',
            'email'    => 'admin@rus.ac.th',
            'password' => Hash::make('password'),
            'name_th'  => 'ผู้ดูแลระบบ',
            'name_en'  => 'Super Admin',
            'title_th' => '',
            'title_en' => '',
            'role'     => User::ROLE_SUPER_ADMIN,
            'preferred_language' => 'th',
            'email_verified_at'  => now(),
        ]);

        $this->command->info('Default super admin created: admin@rus.ac.th / password');
        $this->command->warn('IMPORTANT: Change this password immediately in production!');
    }
}
