<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Konselors extends Model
{

    use Notifiable;

    protected $fillable = [
        'user_id',
        'is_active',
        'nip',
        'phone',
        'jenis_kelamin',
        'foto_profil',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jadwalKonselor()
    {
        return $this->hasMany(JadwalKonselors::class);
    }

    public function tikets()
    {
        return $this->hasMany(tikets::class);
    }

    public function sesiKonselings()
    {
        return $this->hasMany(SesiKonselings::class);
    }
}
