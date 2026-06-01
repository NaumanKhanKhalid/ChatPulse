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
            ['name'=>'Admin User','email'=>'admin@chatpulse.app','username'=>'admin','role'=>'admin'],
            ['name'=>'Sara Khan','email'=>'sara@chatpulse.app','username'=>'sara'],
            ['name'=>'Ahmed Raza','email'=>'ahmed@chatpulse.app','username'=>'ahmed'],
            ['name'=>'Fatima Ali','email'=>'fatima@chatpulse.app','username'=>'fatima'],
            ['name'=>'Usman Tariq','email'=>'usman@chatpulse.app','username'=>'usman'],
            ['name'=>'Ali Hassan','email'=>'ali@chatpulse.app','username'=>'ali'],
            ['name'=>'Maria Baig','email'=>'maria@chatpulse.app','username'=>'maria'],
            ['name'=>'Zara Sheikh','email'=>'zara@chatpulse.app','username'=>'zara'],
            ['name'=>'Omar Farooq','email'=>'omar@chatpulse.app','username'=>'omar'],
            ['name'=>'Hina Malik','email'=>'hina@chatpulse.app','username'=>'hina'],
            ['name'=>'Bilal Chaudhry','email'=>'bilal@chatpulse.app','username'=>'bilal'],
        ];

        foreach ($users as $data) {
            User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'username' => $data['username'],
                'role' => $data['role'] ?? 'user',
                'status_type' => 'available',
                'bio' => 'Software engineer at ChatPulse team.',
            ]);
        }
    }
}
