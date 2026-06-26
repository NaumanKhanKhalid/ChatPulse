<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ConversationSeeder::class,
            MessageSeeder::class,
            PollSeeder::class,
            ReactionSeeder::class,
            CallSeeder::class,
        ]);
    }
}
