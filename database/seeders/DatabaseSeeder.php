<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'login_id' => 'admin',
            'name' => 'Administrator',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
            'status' => 'Aktif',
        ]);
    }
}
