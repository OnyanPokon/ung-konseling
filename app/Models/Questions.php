<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Questions extends Model
{
    protected $fillable = [
        'assessment_id',
        'question_text',
        'scale',
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessments::class, 'assessment_id'); 
    }

    public function responseDetails()
    {
        return $this->hasMany(ResponseDetails::class);
    }
}
