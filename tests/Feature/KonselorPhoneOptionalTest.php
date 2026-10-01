<?php

namespace Tests\Feature;

use App\Models\HariLayanans;
use App\Models\Konselis;
use App\Models\Konselors;
use App\Models\SesiKonselings;
use App\Models\Tikets;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * A counselor is not required to have a phone number. The column and the
 * validation rule used to disagree about that, and registration was the place
 * the disagreement surfaced.
 */
class KonselorPhoneOptionalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        Notification::fake();

        Http::preventStrayRequests();

        Sanctum::actingAs(User::factory()->create()->assignRole('admin'));
    }

    public function test_a_counselor_can_be_registered_without_a_phone_number(): void
    {
        $this->registerACounselor()->assertCreated();

        $this->assertNull(Konselors::sole()->phone);
    }

    public function test_a_counselor_registered_without_a_phone_can_still_be_listed_and_shown(): void
    {
        $this->registerACounselor()->assertCreated();

        $id = Konselors::sole()->id;

        $this->getJson('/api/konselor')->assertOk();
        $this->getJson("/api/konselor/{$id}")->assertOk();
    }

    public function test_a_counselor_with_no_phone_number_can_take_a_session(): void
    {
        $counselor = $this->aRegisteredCounselorWithoutAPhone();
        $tiket = $this->anApprovedTicketFor($counselor);

        $this->scheduleASessionFor($counselor, $tiket)->assertCreated();

        $this->assertSame($counselor->id, SesiKonselings::sole()->konselor_id);
    }

    public function test_a_counselor_with_no_phone_number_does_not_stop_the_konseli_being_notified(): void
    {
        $counselor = $this->aRegisteredCounselorWithoutAPhone();
        $tiket = $this->anApprovedTicketFor($counselor);

        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'detail' => 'success'])]);

        $this->scheduleASessionFor($counselor, $tiket)->assertCreated();

        // The missing number belongs to the counselor, not the konseli, so it
        // must not cost the konseli the message they are waiting on.
        Http::assertSent(fn (Request $request) => $request['target'] === '6281234567890');
    }

    public function test_every_accepted_number_format_still_registers(): void
    {
        foreach (['081234567890', '6281234567890', '+6281234567890', '(0812) 3456-7890'] as $format) {
            $this->registerACounselor([
                'email' => 'konselor'.md5($format).'@ung.go.id',
                'nip' => '19800101200501'.substr(md5($format), 0, 4),
                'phone' => $format,
            ])->assertCreated();
        }

        $this->assertCount(4, Konselors::all());
    }

    public function test_a_registered_number_is_stored_exactly_as_it_was_typed(): void
    {
        // Normalization is the gateway client's job at send time, not something
        // the database is silently rewritten by. Whether a format is accepted
        // and whether it is rewritten on the way in are two separate questions.
        $this->registerACounselor(['phone' => '(0812) 3456-7890'])->assertCreated();

        $this->assertSame('(0812) 3456-7890', Konselors::sole()->phone);
    }

    private function aRegisteredCounselorWithoutAPhone(): Konselors
    {
        $this->registerACounselor()->assertCreated();

        return Konselors::sole();
    }

    private function scheduleASessionFor(Konselors $counselor, Tikets $tiket): TestResponse
    {
        Sanctum::actingAs($counselor->user);

        return $this->postJson('/api/sesi_konseling', [
            'tiket_id' => $tiket->id,
            'konselor_id' => $counselor->id,
            'hari_layanan_id' => $tiket->hari_layanan_id,
            'tanggal_konseling' => '2026-10-05',
            'jam_mulai' => '10:00',
            'jam_selesai' => '11:00',
            'tempat' => 'Ruang Konseling Lt. 2',
            'status' => 'dijadwalkan',
        ]);
    }

    private function anApprovedTicketFor(Konselors $counselor): Tikets
    {
        $konseli = Konselis::create([
            'user_id' => User::factory()->create(['name' => 'Sitti Rahma'])->id,
            'nim' => '221011000001',
            'phone' => '081234567890',
            'domisili' => 'Gorontalo',
            'jurusan' => 'Teknik Informatika',
            'umur' => 21,
            'jenis_kelamin' => 'P',
        ]);

        return Tikets::create([
            'konseli_id' => $konseli->id,
            'konselor_id' => $counselor->id,
            'hari_layanan_id' => HariLayanans::create(['hari' => 'senin'])->id,
            'deskripsi_keluhan' => 'Kesulitan belajar',
            'jenis_layanan' => 'konseling',
            'jenis_keluhan' => 'akademik',
            'status' => 'approved',
        ]);
    }

    private function registerACounselor(array $overrides = []): TestResponse
    {
        return $this->postJson('/api/konselor', array_merge([
            'nama' => 'Rina Marlina',
            'email' => 'rina.marline@ung.go.id',
            'password' => 'rahasia123',
            'nip' => '198001012005011001',
            'jenis_kelamin' => 'P',
            'is_active' => true,
        ], $overrides));
    }
}
