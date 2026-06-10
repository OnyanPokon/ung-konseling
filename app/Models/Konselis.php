<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Konselis extends Model
{
    use Notifiable;
    
    protected $fillable = [
        'user_id',
        'nim',
        'phone',
        'domisili',
        'jurusan',
        'umur',
        'jenis_kelamin',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tikets()
    {
        return $this->hasMany(Tikets::class);
    }
}
