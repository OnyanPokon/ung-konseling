<?php

namespace Database\Seeders;

use App\Models\Konselis;
use App\Models\konselors;
use App\Models\User;
use Illuminate\Database\Seeder;


class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ===== ADMIN =====
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@app.id',
            'password' => bcrypt('password'),
        ]);

        $admin->assignRole('admin');

        // ===== KONSELOR =====
        $konselorUser = User::create([
            'name' => 'Budi Konselor',
            'email' => 'konselor@app.id',
            'password' => bcrypt('password'),
        ]);

        $konselorUser->assignRole('konselor');

        konselors::create([
            'user_id' => $konselorUser->id,
            'nip' => '12345678',
            'phone' => '08123456789',
            'jenis_kelamin' => 'L',
            
            'is_active' => true,
        ]);

        // ===== KONSELI =====
        $konseliUser = User::create([
            'name' => 'Andi Konseli',
            'email' => 'konseli@app.id',
            'password' => bcrypt('password'),
        ]);

        $konseliUser->assignRole('konseli');

        Konselis::create([
            'user_id' => $konseliUser->id,
            'nim' => '12345678',
            'phone' => '08123456789',
            'domisili' => 'Jakarta',
            'jurusan' => 'Teknik Informatika',
            'umur' => 20,
            'jenis_kelamin' => 'L',
        ]);
    }
}
