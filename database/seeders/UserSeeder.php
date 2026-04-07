<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'System Admin',
                'email' => 'admin@pharmacy.local',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
            [
                'name' => 'Lead Pharmacist',
                'email' => 'pharmacist@pharmacy.local',
                'password' => Hash::make('password'),
                'role' => 'pharmacist',
            ],
            [
                'name' => 'Front Desk Staff',
                'email' => 'staff@pharmacy.local',
                'password' => Hash::make('password'),
                'role' => 'staff',
            ],
        ];

        foreach ($users as $seededUser) {
            $role = $seededUser['role'];
            unset($seededUser['role']);

            $user = User::updateOrCreate(
                ['email' => $seededUser['email']],
                $seededUser
            );

            $user->syncRoles([$role]);
        }
    }
}
