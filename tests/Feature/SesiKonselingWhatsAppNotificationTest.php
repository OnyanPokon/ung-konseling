<?php

namespace Tests\Feature;

use App\Models\HariLayanans;
use App\Models\Konselis;
use App\Models\Konselors;
use App\Models\SesiKonselings;
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
 * Every test here drives the public session endpoints with the outbound HTTP
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

    private const TANGGAL_BARU = '2026-10-12';

    private const TANGGAL_BARU_INDONESIA = 'Senin, 12 Oktober 2026';

    private const JAM_MULAI_BARU = '14:00';

    private const JAM_SELESAI_BARU = '15:00';

    private const TEMPAT_BARU = 'Ruang Konseling Lt. 3';

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

    public function test_every_format_already_stored_against_a_konseli_reaches_the_same_target(): void
    {
        // Numbers reach the database in whichever format the person typed, and
        // historical records were never rewritten. Each one has to deliver.
        foreach (['081234567890', '6281234567890', '+6281234567890', '(0812) 3456-7890'] as $stored) {
            $this->konseli->update(['phone' => $stored]);

            $this->gatewayAccepts();

            $this->scheduleSession()->assertCreated();

            // Asserting against the recording as a whole would let one format
            // pass on another's delivery, so each attempt is read on its own.
            $this->assertSame(self::KONSELI_DIALABLE, $this->lastTargetSent());
        }
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

    public function test_the_schedule_email_greets_the_konseli_by_name(): void
    {
        $this->gatewayAccepts();

        $this->scheduleSession();

        Notification::assertSentTo($this->konseliUser, SesiKonselingCreated::class, function ($notification) {
            $mail = $notification->toMail($this->konseliUser);
            
            return str_contains($mail->greeting, self::KONSELI_NAME);
        });
    }

    public function test_moving_the_date_tells_the_konseli_the_new_date(): void
    {
        $this->gatewayAccepts();
        $sesi = $this->scheduledSession();

        $this->editTheSessionToANewDate($sesi)->assertOk();

        $this->assertStringContainsString(
            self::TANGGAL_BARU_INDONESIA,
            $this->lastMessageSentToTheKonseli()
        );
    }

    public function test_moving_the_time_tells_the_konseli_the_new_time(): void
    {
        $this->gatewayAccepts();
        $sesi = $this->scheduledSession();

        $this->editTheSessionToANewTime($sesi)->assertOk();

        $message = $this->lastMessageSentToTheKonseli();

        $this->assertStringContainsString(self::JAM_MULAI_BARU, $message);
        $this->assertStringContainsString(self::JAM_SELESAI_BARU, $message);
    }

    public function test_moving_the_room_tells_the_konseli_the_new_room(): void
    {
        $this->gatewayAccepts();
        $sesi = $this->scheduledSession();

        $this->editTheSessionToANewRoom($sesi)->assertOk();

        $this->assertStringContainsString(
            self::TEMPAT_BARU,
            $this->lastMessageSentToTheKonseli()
        );
    }

    public function test_a_moved_session_message_carries_the_whole_new_schedule(): void
    {
        $this->gatewayAccepts();
        $sesi = $this->scheduledSession();

        $this->editTheSession($sesi, [
            'tanggal_konseling' => self::TANGGAL_BARU,
            'jam_mulai' => self::JAM_MULAI_BARU,
            'jam_selesai' => self::JAM_SELESAI_BARU,
            'tempat' => self::TEMPAT_BARU,
        ])->assertOk();

        $message = $this->lastMessageSentToTheKonseli();

        $this->assertStringContainsString(self::TANGGAL_BARU_INDONESIA, $message);
        $this->assertStringContainsString(self::JAM_MULAI_BARU, $message);
        $this->assertStringContainsString(self::JAM_SELESAI_BARU, $message);
        $this->assertStringContainsString(self::TEMPAT_BARU, $message);
    }

    public function test_a_moved_session_is_not_reported_as_a_second_approved_session(): void
    {
        $this->gatewayAccepts();
        $sesi = $this->scheduledSession();

        $approvalMessage = $this->lastMessageSentToTheKonseli();

        $this->editTheSessionToANewDate($sesi)->assertOk();

        $movedMessage = $this->lastMessageSentToTheKonseli();

        $this->assertNotSame($approvalMessage, $movedMessage);
        $this->assertStringContainsString('dipindahkan', $movedMessage);
        $this->assertStringNotContainsString('disetujui', $movedMessage);
    }

    public function test_a_moved_session_message_still_carries_the_name_and_the_signing_unit(): void
    {
        $this->gatewayAccepts();
        $sesi = $this->scheduledSession();

        $this->editTheSessionToANewDate($sesi)->assertOk();

        $message = $this->lastMessageSentToTheKonseli();

        $this->assertStringContainsString(self::KONSELI_NAME, $message);
        $this->assertStringContainsString(config('message.whatsapp.unit_name'), $message);
        $this->assertStringContainsString(
            'Pesan ini dikirim otomatis, mohon tidak dibalas.',
            $message
        );
    }

    public function test_editing_a_field_outside_the_schedule_sends_nothing(): void
    {
        $this->gatewayAccepts();
        $sesi = $this->scheduledSession();

        $this->assertTheEditSentNoFurtherMessage(
            fn () => $this->replaceTheCounselor($sesi)
        );
    }

    public function test_marking_a_session_selesai_sends_nothing(): void
    {
        $this->gatewayAccepts();
        $sesi = $this->scheduledSession();

        $this->assertTheEditSentNoFurtherMessage(
            fn () => $this->editTheSession($sesi, ['status' => 'selesai'])
        );
    }

    public function test_resubmitting_the_schedule_unchanged_sends_nothing(): void
    {
        $this->gatewayAccepts();
        $sesi = $this->scheduledSession();

        // The frontend sends the whole record back on every edit, so the values
        // a reschedule is judged on have to be compared, not merely received.
        $this->assertTheEditSentNoFurtherMessage(fn () => $this->editTheSession($sesi, [
            'tanggal_konseling' => self::TANGGAL,
            'jam_mulai' => self::JAM_MULAI,
            'jam_selesai' => self::JAM_SELESAI,
            'tempat' => self::TEMPAT,
        ]));
    }

    public function test_the_reschedule_is_committed_before_the_gateway_is_called(): void
    {
        $transactionsOpenWhileSending = null;

        Http::fake(function () use (&$transactionsOpenWhileSending) {
            $transactionsOpenWhileSending = DB::connection()->transactionLevel();

            return Http::response(self::GATEWAY_ACCEPTS);
        });

        $sesi = $this->scheduledSession();

        $transactionsBeforeTheEdit = DB::connection()->transactionLevel();

        $this->editTheSessionToANewDate($sesi)->assertOk();

        // Two sends, so the level read back is the one the reschedule was
        // attempted at rather than the approval's.
        Http::assertSentCount(2);

        $this->assertSame($transactionsBeforeTheEdit, $transactionsOpenWhileSending);
    }

    public function test_the_new_schedule_is_saved_when_the_gateway_rejects_the_message(): void
    {
        $this->gatewayRejects();

        $sesi = $this->scheduledSession();

        $this->editTheSessionToANewDate($sesi)->assertOk();

        $this->getJson('/api/sesi_konseling')
            ->assertOk()
            ->assertJsonFragment([
                'tempat' => self::TEMPAT,
                'tanggal_konseling' => self::TANGGAL_BARU,
            ]);
    }

    public function test_the_new_schedule_is_saved_when_the_konselis_phone_number_is_malformed(): void
    {
        $this->gatewayAccepts();
        $this->konseli->update(['phone' => 'bukan nomor']);

        $sesi = $this->scheduledSession();

        $this->editTheSessionToANewDate($sesi)->assertOk();

        $this->getJson('/api/sesi_konseling')
            ->assertOk()
            ->assertJsonFragment([
                'tempat' => self::TEMPAT,
                'tanggal_konseling' => self::TANGGAL_BARU,
            ]);
    }

    public function test_a_failed_reschedule_send_is_logged_as_a_reschedule(): void
    {
        $this->gatewayRejects();

        $sesi = $this->scheduledSession();

        Log::spy();

        $this->editTheSessionToANewDate($sesi)->assertOk();

        Log::shouldHaveReceived('error')
            ->once()
            ->withArgs(function ($message, $context) {
                return $context['target'] === self::KONSELI_DIALABLE
                    && $context['event'] === 'reschedule'
                    && $context['reason'] === 'insufficient quota';
            });
    }

    public function test_cancelling_a_session_tells_the_konseli_it_was_cancelled(): void
    {
        $this->gatewayAccepts();
        $sesi = $this->scheduledSession();

        $this->cancelTheSession($sesi)->assertOk();

        $this->assertStringContainsString('dibatalkan', $this->lastMessageSentToTheKonseli());
    }

    public function test_a_cancelled_session_is_neither_approved_nor_moved(): void
    {
        $this->gatewayAccepts();
        $sesi = $this->scheduledSession();

        $approvalMessage = $this->lastMessageSentToTheKonseli();

        $this->editTheSessionToANewDate($sesi)->assertOk();

        $movedMessage = $this->lastMessageSentToTheKonseli();

        $this->cancelTheSession($sesi)->assertOk();

        $cancelledMessage = $this->lastMessageSentToTheKonseli();

        $this->assertNotSame($approvalMessage, $cancelledMessage);
        $this->assertNotSame($movedMessage, $cancelledMessage);
        $this->assertStringNotContainsString('disetujui', $cancelledMessage);
        $this->assertStringNotContainsString('dipindahkan', $cancelledMessage);
    }

    public function test_a_cancelled_session_message_still_carries_the_name_and_the_signed_details(): void
    {
        $this->gatewayAccepts();
        $sesi = $this->scheduledSession();

        $this->cancelTheSession($sesi)->assertOk();

        $message = $this->lastMessageSentToTheKonseli();

        // A konseli with more than one session needs to be told which one is
        // off, so the cancelled date travels with the cancellation.
        $this->assertStringContainsString(self::KONSELI_NAME, $message);
        $this->assertStringContainsString(self::TANGGAL_INDONESIA, $message);
        $this->assertStringContainsString(self::TEMPAT, $message);
        $this->assertStringContainsString(config('message.whatsapp.unit_name'), $message);
        $this->assertStringContainsString(
            'Pesan ini dikirim otomatis, mohon tidak dibalas.',
            $message
        );
    }

    public function test_a_status_change_to_another_value_sends_nothing(): void
    {
        $this->gatewayAccepts();
        $sesi = $this->scheduledSession();

        $this->assertTheEditSentNoFurtherMessage(
            fn () => $this->editTheSession($sesi, ['status' => 'dijadwalkan_ulang'])
        );
    }

    public function test_resubmitting_a_cancelled_session_sends_nothing(): void
    {
        $this->gatewayAccepts();
        $sesi = $this->scheduledSession();

        $this->cancelTheSession($sesi)->assertOk();

        // The frontend sends the whole record back on every edit, so a session
        // already marked dibatalkan must not re-announce its own cancellation.
        $this->assertTheEditSentNoFurtherMessage(
            fn () => $this->cancelTheSession($sesi)
        );
    }

    public function test_cancelling_and_moving_in_one_request_sends_only_the_cancellation(): void
    {
        $this->gatewayAccepts();
        $sesi = $this->scheduledSession();

        $this->editTheSession($sesi, [
            'tanggal_konseling' => self::TANGGAL_BARU,
            'status' => 'dibatalkan',
        ])->assertOk();

        // Two sends: the approval and the cancellation. A third would tell the
        // konseli their session moved and then, separately, that it is off.
        Http::assertSentCount(2);

        $this->assertStringContainsString('dibatalkan', $this->lastMessageSentToTheKonseli());
    }

    public function test_a_cancelled_session_sends_nothing_when_its_fields_are_tidied_up(): void
    {
        $this->gatewayAccepts();
        $sesi = $this->scheduledSession();

        $this->cancelTheSession($sesi)->assertOk();

        // "Moved" would contradict the cancellation the konseli was just sent.
        $this->assertTheEditSentNoFurtherMessage(
            fn () => $this->editTheSession($sesi, ['tempat' => self::TEMPAT_BARU])
        );
    }

    public function test_restoring_a_cancelled_session_to_dijadwalkan_sends_nothing(): void
    {
        $this->gatewayAccepts();
        $sesi = $this->scheduledSession();

        $this->cancelTheSession($sesi)->assertOk();

        $this->assertTheEditSentNoFurtherMessage(
            fn () => $this->editTheSession($sesi, ['status' => 'dijadwalkan'])
        );
    }

    public function test_the_cancellation_is_committed_before_the_gateway_is_called(): void
    {
        $transactionsOpenWhileSending = null;

        Http::fake(function () use (&$transactionsOpenWhileSending) {
            $transactionsOpenWhileSending = DB::connection()->transactionLevel();

            return Http::response(self::GATEWAY_ACCEPTS);
        });

        $sesi = $this->scheduledSession();

        $transactionsBeforeTheEdit = DB::connection()->transactionLevel();

        $this->cancelTheSession($sesi)->assertOk();

        // Two sends, so the level read back is the one the cancellation was
        // attempted at rather than the approval's.
        Http::assertSentCount(2);

        $this->assertSame($transactionsBeforeTheEdit, $transactionsOpenWhileSending);
    }

    public function test_the_cancellation_is_saved_when_the_gateway_rejects_the_message(): void
    {
        $this->gatewayRejects();
        $sesi = $this->scheduledSession();

        $this->cancelTheSession($sesi)->assertOk();

        $this->getJson('/api/sesi_konseling')
            ->assertOk()
            ->assertJsonFragment(['status' => 'dibatalkan']);
    }

    public function test_a_failed_cancellation_send_is_logged_as_a_cancellation(): void
    {
        $this->gatewayRejects();
        $sesi = $this->scheduledSession();

        Log::spy();

        $this->cancelTheSession($sesi)->assertOk();

        Log::shouldHaveReceived('error')
            ->once()
            ->withArgs(function ($message, $context) {
                return $context['target'] === self::KONSELI_DIALABLE
                    && $context['event'] === 'cancellation'
                    && $context['reason'] === 'insufficient quota';
            });
    }

    private function assertTheAppointmentIsReadable(): void
    {
        $this->getJson('/api/sesi_konseling')
            ->assertOk()
            ->assertJsonFragment(['tempat' => self::TEMPAT, 'tanggal_konseling' => self::TANGGAL]);
    }

    private function assertTheEditSentNoFurtherMessage(callable $edit): void
    {
        // The approval message is the baseline. An empty one would make the
        // comparison below pass without a single edit ever being made.
        $messagesBeforeTheEdit = $this->messagesSentToTheKonseli();

        $this->assertNotEmpty($messagesBeforeTheEdit);

        $edit();

        $this->assertSame($messagesBeforeTheEdit, $this->messagesSentToTheKonseli());
    }

    private function messagesSentToTheKonseli(): array
    {
        return Http::recorded()
            ->map(fn (array $exchange) => $exchange[0]['message'])
            ->all();
    }

    private function lastMessageSentToTheKonseli(): string
    {
        $messages = $this->messagesSentToTheKonseli();

        $this->assertNotEmpty($messages);

        return end($messages);
    }

    private function lastTargetSent(): ?string
    {
        $targets = Http::recorded()
            ->map(fn (array $exchange) => $exchange[0]['target'])
            ->all();

        $this->assertNotEmpty($targets);

        return end($targets);
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

    private function scheduledSession(): SesiKonselings
    {
        $this->scheduleSession()->assertCreated();

        return SesiKonselings::sole();
    }

    private function editTheSession(SesiKonselings $sesi, array $changes): TestResponse
    {
        return $this->putJson('/api/sesi_konseling/'.$sesi->id, $changes);
    }

    private function editTheSessionToANewDate(SesiKonselings $sesi): TestResponse
    {
        return $this->editTheSession($sesi, ['tanggal_konseling' => self::TANGGAL_BARU]);
    }

    private function editTheSessionToANewTime(SesiKonselings $sesi): TestResponse
    {
        return $this->editTheSession($sesi, [
            'jam_mulai' => self::JAM_MULAI_BARU,
            'jam_selesai' => self::JAM_SELESAI_BARU,
        ]);
    }

    private function editTheSessionToANewRoom(SesiKonselings $sesi): TestResponse
    {
        return $this->editTheSession($sesi, ['tempat' => self::TEMPAT_BARU]);
    }

    private function cancelTheSession(SesiKonselings $sesi): TestResponse
    {
        return $this->editTheSession($sesi, ['status' => 'dibatalkan']);
    }

    private function replaceTheCounselor(SesiKonselings $sesi): TestResponse
    {
        $otherCounselor = Konselors::create([
            'user_id' => User::factory()->create(['name' => 'Dewi Ananta'])->id,
            'nip' => '198001012005011002',
            'phone' => '081200000001',
            'jenis_kelamin' => 'P',
        ]);

        return $this->editTheSession($sesi, ['konselor_id' => $otherCounselor->id]);
    }
}
