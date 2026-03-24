<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResponseDetails extends Model
{
    protected $fillable = [
        'response_id',
        'question_id',
        'score',
    ];

    public function response()
    {
        return $this->belongsTo(Responses::class, 'response_id');
    }

    public function question()
    {
        return $this->belongsTo(Questions::class);
    }
}
