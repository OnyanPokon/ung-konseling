<?php

namespace Tests\Feature;

use App\Models\HariLayanans;
use App\Models\Konselis;
use App\Models\Konselors;
use App\Models\Tikets;
use App\Models\User;
use App\Notifications\TiketRejected;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TiketRejectedNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        Notification::fake();
    }

    public function test_rejected_ticket_email_greets_konseli_by_name(): void
    {
        $konseliUser = User::factory()->create(['name' => 'Budi Santoso']);
        $konseli = Konselis::create([
            'user_id' => $konseliUser->id,
            'nim' => '221011000002',
            'phone' => '081234567891',
            'domisili' => 'Gorontalo',
            'jurusan' => 'Teknik Informatika',
            'umur' => 21,
            'jenis_kelamin' => 'L',
        ]);

        $counselorUser = User::factory()->create(['name' => 'Rina Marlina']);
        $counselorUser->assignRole('konselor');
        $counselor = Konselors::create([
            'user_id' => $counselorUser->id,
            'nip' => '198001012005011001',
            'phone' => '081234567892',
            'jenis_kelamin' => 'P',
        ]);

        $tiket = Tikets::create([
            'konseli_id' => $konseli->id,
            'konselor_id' => $counselor->id,
            'hari_layanan_id' => HariLayanans::create(['hari' => 'senin'])->id,
            'deskripsi_keluhan' => 'Kesulitan belajar',
            'jenis_layanan' => 'konseling',
            'jenis_keluhan' => 'akademik',
            'status' => 'pending',
        ]);

        Sanctum::actingAs($counselorUser);

        $this->putJson("/api/tiket/{$tiket->id}", [
            'status' => 'rejected',
        ])->assertOk();

        Notification::assertSentTo($konseliUser, TiketRejected::class, function ($notification) use ($konseliUser) {
            $mail = $notification->toMail($konseliUser);

            return str_contains($mail->greeting, 'Halo Budi Santoso');
        });
    }
}
