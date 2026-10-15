<?php

namespace Database\Seeders;

use App\Models\HariLayanans;
use App\Models\Konselis;
use App\Models\konselors;
use App\Models\Laisegs;
use App\Models\SesiKonselings;
use App\Models\Tikets;
use App\Models\User;
use Illuminate\Database\Seeder;

class LaisegTestSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Pastikan RolePermission dijalankan terlebih dahulu
        $this->call([
            RolePermissionSeeder::class,
        ]);

        // Cek atau buat user Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@app.id'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
            ]
        );
        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // Cek atau buat user Konselor
        $konselorUser = User::firstOrCreate(
            ['email' => 'konselor@app.id'],
            [
                'name' => 'Budi Konselor',
                'password' => bcrypt('password'),
            ]
        );
        if (! $konselorUser->hasRole('konselor')) {
            $konselorUser->assignRole('konselor');
        }

        $konselor = konselors::firstOrCreate(
            ['user_id' => $konselorUser->id],
            [
                'nip' => '12345678',
                'phone' => '08123456789',
                'jenis_kelamin' => 'L',
                'is_active' => true,
            ]
        );

        // Cek atau buat user Konseli
        $konseliUser = User::firstOrCreate(
            ['email' => 'konseli@app.id'],
            [
                'name' => 'Andi Konseli',
                'password' => bcrypt('password'),
            ]
        );
        if (! $konseliUser->hasRole('konseli')) {
            $konseliUser->assignRole('konseli');
        }

        $konseli = Konselis::firstOrCreate(
            ['user_id' => $konseliUser->id],
            [
                'nim' => '12345678',
                'phone' => '08123456789',
                'domisili' => 'Jakarta',
                'jurusan' => 'Teknik Informatika',
                'umur' => 20,
                'jenis_kelamin' => 'L',
            ]
        );

        // 3. Buat atau ambil Hari Layanan
        $hariLayanan = HariLayanans::firstOrCreate(
            ['hari' => 'senin']
        );

        // 4. Buat Tiket Konseling (status approved)
        $tiket = Tikets::create([
            'konseli_id' => $konseli->id,
            'konselor_id' => $konselor->id,
            'hari_layanan_id' => $hariLayanan->id,
            'deskripsi_keluhan' => 'Saya merasa cemas dengan akademik saya semester ini.',
            'jenis_layanan' => 'konseling',
            'jenis_keluhan' => 'akademik',
            'status' => 'approved',
        ]);

        // 5. Buat Sesi Konseling (status selesai, agar tombol Laiseg aktif di UI)
        $sesiKonseling = SesiKonselings::create([
            'tiket_id' => $tiket->id,
            'konselor_id' => $konselor->id,
            'hari_layanan_id' => $hariLayanan->id,
            'tanggal_konseling' => now()->toDateString(),
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '10:00:00',
            'tempat' => 'Ruang Konseling 1',
            'status' => 'selesai',
        ]);

        $this->command->info('Berhasil membuat Sesi Konseling ID: ' . $sesiKonseling->id . ' untuk diuji pada formulir Laiseg.');
        $this->command->info('Login sebagai konseli (email: konseli@app.id, password: password) lalu akses sesi konseling untuk mengisi Laiseg.');
    }
}
