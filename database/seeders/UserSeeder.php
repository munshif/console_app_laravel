<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * User Seeder
     * @return void
     */
    public function run()
    {
        User::query()->truncate();

        User::factory()->create([
            'name' => 'Munshif',
            'email' => 'munshif@test.com',
        ]);

        User::factory()->create([
            'name' => 'Jhone',
            'email' => 'jhone@test.com',
        ]);
    }
}
