<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'cedula' => 24377317,
            'nombre' => 'Ramon',
            'apellido' => 'Maita',
            // 'name' => 'Ramón Maita',
            'email' => 'admin@gmail.com',
            'password' => Hash::make("maita123486"),
        ]);

        $this->call(RolesAndPermissionsSeeder::class);
    }
}
