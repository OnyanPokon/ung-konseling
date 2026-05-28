<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TiketRejected extends Notification implements ShouldBroadcast
{
    use Queueable;

    protected $tiket;

    public function __construct($tiket)
    {
        $this->tiket = $tiket;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Pengajuan Tiket Ditolak')
            ->greeting('Halo ' . $notifiable->nama)
            ->line('Mohon maaf, pengajuan layanan konseling Anda ditolak.')
            ->line('Nomor tiket: ' . $this->tiket->nomor_tiket)
            ->line('Silakan login ke sistem untuk melihat detail lebih lanjut.');
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Tiket Ditolak',
            'message' => 'Pengajuan tiket Anda ditolak',
            'tiket_id' => $this->tiket->id
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => 'Tiket Ditolak',
            'message' => 'Pengajuan tiket Anda ditolak',
            'tiket_id' => $this->tiket->id
        ]);
    }

    public function broadcastOn()
    {
        return new \Illuminate\Broadcasting\PrivateChannel(
            'User.' . $this->tiket->konseli->user->id
        );
    }
}