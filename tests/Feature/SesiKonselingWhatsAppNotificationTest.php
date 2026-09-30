<?php

namespace Tests\Feature;

use App\Models\HariLayanans;
use App\Models\Konselis;
use App\Models\Konselors;
use App\Models\Tikets;
use App\Models\User;
use App\Notifications\SesiKonselingCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Every test here drives the public schedule endpoint with the outbound HTTP
 * layer faked, asserting on the request that would have reached the gateway —
 * never on internal method calls.
 */
class SesiKonselingWhatsAppNotificationTest extends TestCase
{
    use RefreshDatabase;

    private const KONSELI_NAME = 'Sitti Rahma';

    private const KONSELI_PHONE = '081234567890';

    private const KONSELI_DIALABLE = '6281234567890';

    private const KONSELOR_DIALABLE = '6281200000000';

    private const TANGGAL = '2026-10-05';

    private const TANGGAL_INDONESIA = 'Senin, 5 Oktober 2026';

    private const JAM_MULAI = '10:00';

    private const JAM_SELESAI = '11:00';

    private const TEMPAT = 'Ruang Konseling Lt. 2';

    private const GATEWAY_ACCEPTS = ['status' => true, 'detail' => 'success'];

    private const GATEWAY_REJECTS = ['status' => false, 'reason' => 'insufficient quota'];

    private User $konseliUser;

    private Konselis $konseli;

    private Konselors $konselor;

    private Tikets $tiket;

    private HariLayanans $hariLayanan;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.fonnte.token' => 'fonnte-token-from-config',
            'services.fonnte.country_code' => '62',
        ]);

        Notification::fake();

        // Each test states how the gateway answers; nothing reaches the network.
        Http::preventStrayRequests();

        $this->konseliUser = User::factory()->create(['name' => self::KONSELI_NAME]);

        $this->konseli = Konselis::create([
            'user_id' => $this->konseliUser->id,
            'nim' => '221011000001',
            'phone' => self::KONSELI_PHONE,
            'domisili' => 'Gorontalo',
            'jurusan' => 'Teknik Informatika',
            'umur' => 21,
            'jenis_kelamin' => 'P',
        ]);

        $this->konselor = Konselors::create([
            'user_id' => User::factory()->create(['name' => 'Rina Marlina'])->id,
            'nip' => '198001012005011001',
            'phone' => '081200000000',
            'jenis_kelamin' => 'P',
        ]);

        $this->hariLayanan = HariLayanans::create(['hari' => 'senin']);

        $this->tiket = Tikets::create([
            'konseli_id' => $this->konseli->id,
            'konselor_id' => $this->konselor->id,
            'hari_layanan_id' => $this->hariLayanan->id,
            'deskripsi_keluhan' => 'Kesulitan belajar',
            'jenis_layanan' => 'konseling',
            'jenis_keluhan' => 'akademik',
            'status' => 'approved',
        ]);

        Sanctum::actingAs($this->konselor->user);
    }

    public function test_the_message_is_addressed_to_the_konselis_own_number(): void
    {
        $this->gatewayAccepts();

        $this->scheduleSession();

        Http::assertSent(function (Request $request) {
            return $request['target'] === self::KONSELI_DIALABLE
                && $request['target'] !== self::KONSELOR_DIALABLE;
        });
    }

    public function test_approving_a_ticket_sends_the_konseli_the_confirmed_schedule(): void
    {
        $this->gatewayAccepts();

        $this->scheduleSession()->assertCreated();

        Http::assertSent(function (Request $request) {
            return str_contains($request['message'], self::TANGGAL_INDONESIA)
                && str_contains($request['message'], self::JAM_MULAI)
                && str_contains($request['message'], self::JAM_SELESAI)
                && str_contains($request['message'], self::TEMPAT);
        });
    }

    public function test_the_message_addresses_the_konseli_by_name(): void
    {
        $this->gatewayAccepts();

        $this->scheduleSession();

        Http::assertSent(function (Request $request) {
            return str_contains($request['message'], self::KONSELI_NAME);
        });
    }

    public function test_the_message_dates_the_session_in_indonesian(): void
    {
        $this->gatewayAccepts();

        $this->scheduleSession();

        Http::assertSent(function (Request $request) {
            return str_contains($request['message'], self::TANGGAL_INDONESIA)
                && ! str_contains($request['message'], self::TANGGAL);
        });
    }

    public function test_the_message_says_a_counselor_approved_the_session(): void
    {
        $this->gatewayAccepts();

        $this->scheduleSession();

        Http::assertSent(function (Request $request) {
            return str_contains($request['message'], 'telah disetujui oleh konselor');
        });
    }

    public function test_the_message_is_signed_by_the_unit_this_deployment_ships_with(): void
    {
        $this->gatewayAccepts();

        $this->scheduleSession();

        Http::assertSent(function (Request $request) {
            return str_contains($request['message'], config('message.whatsapp.unit_name'))
                && str_contains($request['message'], config('message.whatsapp.institution'));
        });
    }

    public function test_the_message_is_signed_by_the_unit_named_in_configuration(): void
    {
        config([
            'message.whatsapp.unit_name' => 'Unit Layanan Konseling Terpadu',
            'message.whatsapp.institution' => 'Institut Contoh Negeri',
        ]);

        $this->gatewayAccepts();

        $this->scheduleSession();

        Http::assertSent(function (Request $request) {
            return str_contains($request['message'], 'Unit Layanan Konseling Terpadu')
                && str_contains($request['message'], 'Institut Contoh Negeri');
        });
    }

    public function test_the_message_tells_the_konseli_not_to_reply(): void
    {
        $this->gatewayAccepts();

        $this->scheduleSession();

        Http::assertSent(function (Request $request) {
            return str_contains($request['message'], 'Pesan ini dikirim otomatis, mohon tidak dibalas.');
        });
    }

    public function test_the_schedule_is_committed_before_the_gateway_is_called(): void
    {
        $transactionsBeforeTheRequest = DB::connection()->transactionLevel();
        $transactionsOpenWhileSending = null;

        Http::fake(function () use (&$transactionsOpenWhileSending) {
            $transactionsOpenWhileSending = DB::connection()->transactionLevel();

            return Http::response(self::GATEWAY_ACCEPTS);
        });

        $this->scheduleSession();

        $this->assertSame($transactionsBeforeTheRequest, $transactionsOpenWhileSending);
    }

    public function test_the_appointment_is_saved_when_the_gateway_rejects_the_message(): void
    {
        $this->gatewayRejects();

        $this->scheduleSession()->assertCreated();

        $this->assertTheAppointmentIsReadable();
    }

    public function test_a_gateway_failure_is_logged_with_the_target_event_reason_and_response(): void
    {
        $this->gatewayRejects();
        Log::spy();

        $this->scheduleSession()->assertCreated();

        Log::shouldHaveReceived('error')
            ->once()
            ->withArgs(function ($message, $context) {
                return $context['target'] === self::KONSELI_DIALABLE
                    && $context['event'] === 'approval'
                    && $context['reason'] === 'insufficient quota'
                    && str_contains($context['response'], 'insufficient quota');
            });
    }

    public function test_the_appointment_is_saved_when_the_konseli_phone_number_is_malformed(): void
    {
        $this->gatewayAccepts();
        $this->konseli->update(['phone' => 'bukan nomor']);

        $this->scheduleSession()->assertCreated();

        $this->assertTheAppointmentIsReadable();
    }

    public function test_no_whatsapp_is_attempted_for_a_malformed_phone_number(): void
    {
        $this->gatewayAccepts();
        $this->konseli->update(['phone' => 'bukan nomor']);

        $this->scheduleSession();

        Http::assertNothingSent();
    }

    public function test_the_schedule_email_still_reaches_the_konseli(): void
    {
        $this->gatewayAccepts();

        $this->scheduleSession();

        Notification::assertSentTo($this->konseliUser, SesiKonselingCreated::class);
    }

    private function assertTheAppointmentIsReadable(): void
    {
        $this->getJson('/api/sesi_konseling')
            ->assertOk()
            ->assertJsonFragment(['tempat' => self::TEMPAT, 'tanggal_konseling' => self::TANGGAL]);
    }

    private function gatewayAccepts(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(self::GATEWAY_ACCEPTS)]);
    }

    private function gatewayRejects(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(self::GATEWAY_REJECTS)]);
    }

    private function scheduleSession(): TestResponse
    {
        return $this->postJson('/api/sesi_konseling', [
            'tiket_id' => $this->tiket->id,
            'konselor_id' => $this->konselor->id,
            'hari_layanan_id' => $this->hariLayanan->id,
            'tanggal_konseling' => self::TANGGAL,
            'jam_mulai' => self::JAM_MULAI,
            'jam_selesai' => self::JAM_SELESAI,
            'tempat' => self::TEMPAT,
            'status' => 'dijadwalkan',
        ]);
    }
}
