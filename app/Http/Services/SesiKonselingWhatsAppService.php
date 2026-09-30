<?php

namespace App\Http\Services;

use App\Enums\SesiKonselingEvent;
use App\Models\SesiKonselings;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Tells the konseli over WhatsApp what happened to their counseling session.
 *
 * Every send here is best-effort: the schedule is already committed by the time
 * these methods run, so nothing here may surface as a failure to the counselor
 * who changed it.
 */
class SesiKonselingWhatsAppService
{
    protected $fonnteService;

    public function __construct(FonnteService $fonnteService)
    {
        $this->fonnteService = $fonnteService;
    }

    public function sendApproval(SesiKonselings $sesi): void
    {
        $this->send($sesi, SesiKonselingEvent::Approval);
    }

    public function sendReschedule(SesiKonselings $sesi): void
    {
        $this->send($sesi, SesiKonselingEvent::Reschedule);
    }

    private function send(SesiKonselings $sesi, SesiKonselingEvent $event): void
    {
        $target = null;

        // Catching Throwable rather than Exception: loading the recipient and
        // building the message must not be able to fail the request that
        // triggered it either, and a TypeError from a bad record would
        // otherwise become a 400 for a session already committed.
        try {
            $target = $sesi->tiket->konseli->phone;

            $this->fonnteService->sendMessage(
                $target,
                $this->message($sesi, $event),
                $event
            );
        } catch (Throwable $e) {
            Log::error('WhatsApp notification could not be attempted.', [
                'event' => $event->value,
                'target' => $target,
                'reason' => $e->getMessage(),
                'response' => null,
            ]);
        }
    }

    private function message(SesiKonselings $sesi, SesiKonselingEvent $event): string
    {
        return implode("\n", [
            'Yang hormat '.$sesi->tiket->konseli->user->name.',',
            '',
            $this->headline($event),
            'Berikut detail jadwalnya:',
            '',
            'Tanggal: '.$this->tanggal($sesi),
            'Waktu  : '.$sesi->jam_mulai.' - '.$sesi->jam_selesai,
            'Tempat : '.$sesi->tempat,
            '',
            config('message.whatsapp.unit_name'),
            config('message.whatsapp.institution'),
            '',
            'Pesan ini dikirim otomatis, mohon tidak dibalas.',
        ]);
    }

    /**
     * What the konseli is told happened. Each event names its own outcome so a
     * moved session cannot read as a second session being approved.
     */
    private function headline(SesiKonselingEvent $event): string
    {
        return match ($event) {
            SesiKonselingEvent::Approval => 'Sesi konseling yang Anda ajukan telah disetujui oleh konselor.',
            SesiKonselingEvent::Reschedule => 'Sesi konseling Anda telah dipindahkan oleh konselor.',
        };
    }

    private function tanggal(SesiKonselings $sesi): string
    {
        return Carbon::parse($sesi->tanggal_konseling)
            ->locale('id')
            ->translatedFormat('l, j F Y');
    }
}
