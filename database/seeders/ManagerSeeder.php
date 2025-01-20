<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Manager;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Manager::create([
            'name' => 'Admin',
            'password' => '123456789',
            'role' => Role::Admin->value
        ]);
    }
}
