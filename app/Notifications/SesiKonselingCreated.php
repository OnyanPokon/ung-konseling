<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SesiKonselingCreated extends Notification implements ShouldBroadcast
{
    use Queueable;

    protected $sesi;

    public function __construct($sesi)
    {
        $this->sesi = $sesi;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Jadwal Konseling Anda Telah Dijadwalkan')
            ->greeting('Halo '.$notifiable->name)
            ->line('Sesi konseling Anda telah dijadwalkan.')
            ->line('Tanggal: '.$this->sesi->tanggal_konseling)
            ->line('Jam: '.$this->sesi->jam_mulai)
            ->line('Tempat: '.$this->sesi->tempat)
            ->line('Silakan login untuk melihat detail.');
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Jadwal Konseling Dibuat',
            'message' => 'Sesi konseling Anda telah dijadwalkan',
            'sesi_id' => $this->sesi->id,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => 'Jadwal Konseling Dibuat',
            'message' => 'Sesi konseling Anda telah dijadwalkan',
            'sesi_id' => $this->sesi->id,
        ]);
    }

    public function broadcastOn()
    {
        return new \Illuminate\Broadcasting\PrivateChannel('User.'.$this->sesi->tiket->konseli->user_id);
    }
}
