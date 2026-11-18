<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DemoAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = strtolower(trim((string) env('DEMO_ADMIN_EMAIL', 'admin@oliyettebook.test')));
        $password = (string) env('DEMO_ADMIN_PASSWORD', 'Admin@123');

        User::updateOrCreate(
            ['email' => $email],
            [
                'first_name' => 'Site',
                'last_name' => 'Administrator',
                'phone' => '09170000001',
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'admin',
                'status' => 'active',
            ]
        );
    }
}
