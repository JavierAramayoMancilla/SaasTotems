<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin'],
            [
                'tenant_id' => null,
                'code' => 'SUPERADMIN',
                'name' => 'Super Administrador',
                'password' => Hash::make('javier2510'),
                'status' => 'active',
            ]
        );

        $user->syncRoles(['superadmin']);
    }
}

