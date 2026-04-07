<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScreeningResponses extends Model
{
    protected $fillable = [
        'screening_id',
        'email',
        'name',
        'nim',
        'institution',
        'major',
    ];

    // 🔗 relasi
    public function screening()
    {
        return $this->belongsTo(Screenings::class, 'screening_id');
    }

    public function details()
    {
        return $this->hasMany(ScreeningResponseDetails::class, 'screening_response_id');
    }
}
