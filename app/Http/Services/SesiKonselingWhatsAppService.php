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
 * who approved it.
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
        $konseli = $sesi->tiket->konseli;
        $event = SesiKonselingEvent::Approval;

        // Catching Throwable rather than Exception: building the message must
        // not be able to fail the approval either, and a TypeError from a bad
        // record would otherwise become a 400 for a session already committed.
        try {
            $this->fonnteService->sendMessage(
                $konseli->phone,
                $this->approvalMessage($sesi),
                $event
            );
        } catch (Throwable $e) {
            Log::error('WhatsApp notification could not be attempted.', [
                'event' => $event->value,
                'target' => $konseli->phone,
                'reason' => $e->getMessage(),
                'response' => null,
            ]);
        }
    }

    private function approvalMessage(SesiKonselings $sesi): string
    {
        return implode("\n", [
            'Yang hormat '.$sesi->tiket->konseli->user->name.',',
            '',
            'Sesi konseling yang Anda ajukan telah disetujui oleh konselor.',
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

    private function tanggal(SesiKonselings $sesi): string
    {
        return Carbon::parse($sesi->tanggal_konseling)
            ->locale('id')
            ->translatedFormat('l, j F Y');
    }
}
