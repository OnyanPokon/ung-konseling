<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TiketCreated extends Notification implements ShouldBroadcast
{

   use Queueable;

    protected $tiket;

    public function __construct($tiket)
    {
        $this->tiket = $tiket;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Tiket baru masuk',
            'message' => 'Tiket baru telah di ajukan',
            'tiket_id' => $this->tiket->id
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => 'Tiket baru masuk',
            'message' => 'Tiket baru telah di ajukan',
            'tiket_id' => $this->tiket->id
        ]);
    }

    public function broadcastOn()
    {
        return new \Illuminate\Broadcasting\PrivateChannel('User.' . $this->tiket->konselor->user->id);
    }
}
