<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Crear usuario administrador por defecto.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@restaurante.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('admin1234'),
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('admin');

        $this->command->info('Usuario admin creado: admin@restaurante.com / admin1234');
    }
}
