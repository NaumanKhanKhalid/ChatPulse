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
                'name'        => 'Admin User',
                'email'       => 'admin@chatpulse.app',
                'username'    => 'admin',
                'role'        => 'admin',
                'bio'         => 'Platform administrator. Here to keep things running.',
                'status_type' => 'available',
                'is_online'   => true,
            ],
            [
                'name'        => 'Sara Karim',
                'email'       => 'sara@chatpulse.app',
                'username'    => 'sara_karim',
                'bio'         => 'Product designer at Northwind Studio. Figma addict ✏️',
                'status_type' => 'available',
                'is_online'   => true,
            ],
            [
                'name'        => 'Ahmed Raza',
                'email'       => 'ahmed@chatpulse.app',
                'username'    => 'ahmed_raza',
                'bio'         => 'Full-stack engineer. Laravel + Alpine.js enthusiast.',
                'status_type' => 'available',
                'is_online'   => true,
            ],
            [
                'name'        => 'Usman Tariq',
                'email'       => 'usman@chatpulse.app',
                'username'    => 'usman_tariq',
                'bio'         => 'Backend dev. Queues, caches, and Reverb.',
                'status_type' => 'busy',
                'is_online'   => true,
            ],
            [
                'name'        => 'Ali Hassan',
                'email'       => 'ali@chatpulse.app',
                'username'    => 'ali_hassan',
                'bio'         => 'UX researcher. Making things usable since 2015.',
                'status_type' => 'available',
                'is_online'   => false,
            ],
            [
                'name'        => 'Fatima Ali',
                'email'       => 'fatima@chatpulse.app',
                'username'    => 'fatima_ali',
                'bio'         => 'QA lead. If it can break, I\'ll find it.',
                'status_type' => 'away',
                'is_online'   => false,
            ],
            [
                'name'        => 'Zara Sheikh',
                'email'       => 'zara@chatpulse.app',
                'username'    => 'zara_sheikh',
                'bio'         => 'UI designer. Pixel-perfect or nothing.',
                'status_type' => 'available',
                'is_online'   => true,
            ],
            [
                'name'        => 'Omar Farooq',
                'email'       => 'omar@chatpulse.app',
                'username'    => 'omar_farooq',
                'bio'         => 'DevOps engineer. Kubernetes + CI/CD.',
                'status_type' => 'available',
                'is_online'   => false,
            ],
            [
                'name'        => 'Hina Malik',
                'email'       => 'hina@chatpulse.app',
                'username'    => 'hina_malik',
                'bio'         => 'Frontend developer. Tailwind is life.',
                'status_type' => 'available',
                'is_online'   => true,
            ],
        ];

        foreach ($users as $data) {
            User::create([
                'name'        => $data['name'],
                'email'       => $data['email'],
                'password'    => Hash::make('password'),
                'username'    => $data['username'],
                'role'        => $data['role'] ?? 'user',
                'status_type' => $data['status_type'],
                'is_online'   => $data['is_online'],
                'bio'         => $data['bio'],
            ]);
        }
    }
}
