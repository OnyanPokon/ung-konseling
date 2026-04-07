<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScreeningResponseDetails extends Model
{
    protected $fillable = [
        'screening_response_id',
        'question_screening_id', // ✅ HARUS ini
        'score',
    ];

    public function response()
    {
        return $this->belongsTo(ScreeningResponses::class, 'screening_response_id');
    }

    public function question()
    {
        return $this->belongsTo(QuestionScreenings::class, 'question_screening_id');
    }
}
