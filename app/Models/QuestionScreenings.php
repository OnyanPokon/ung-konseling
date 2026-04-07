<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionScreenings extends Model
{
    protected $fillable = [
        'screening_id',
        'question_text',
        'scale',
    ];

    public function screening()
    {
        return $this->belongsTo(Screenings::class, 'screening_id');
    }

    public function responseDetails()
    {
        return $this->hasMany(ScreeningResponseDetails::class, 'screening_question_id');
    }
}
