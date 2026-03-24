<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        $admin    = Role::findOrCreate('admin');
        $konselor = Role::findOrCreate('konselor');
        $konseli  = Role::findOrCreate('konseli');

        // Permissions
        $permissions = [
            // admin full
            'manajemen_konseli',
            'manajemen_konselor',
            'manajemen_jadwal_konselor',
            'manajemen_tiket',
            'manajemen_hari_layanan',
            'manajemen_user',
            'manajemen_sesi_konseling',
            'manajemen_artikel',
            'manajemen_periode',
            'manajemen_assessment',
            'manajemen_question',
            'manajemen_response',
            'manajemen_response_detail',

            // konselor
            'read_jadwal_konselor',
            'read_konseli',
            'read_hari_layanan',
            'update_konselor',
            'delete_sesi_konseling',
            'update_sesi_konseling',
            'read_sesi_konseling',

        ];


        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $admin->givePermissionTo([
            'manajemen_konseli',
            'manajemen_konselor',
            'manajemen_jadwal_konselor',
            'manajemen_tiket',
            'manajemen_hari_layanan',
            'manajemen_user',
            'manajemen_artikel',
            'manajemen_periode',
            'manajemen_assessment',
            'manajemen_question',
            'manajemen_response',
            'manajemen_response_detail',
        ]);

        $konselor->givePermissionTo([
            'manajemen_tiket',
            'read_jadwal_konselor',
            'read_konseli',
            'read_hari_layanan',
            'update_konselor',
            'manajemen_artikel',
        ]);

        $konseli->givePermissionTo([]);
    }
}
