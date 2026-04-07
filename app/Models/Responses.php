<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Responses extends Model
{
    protected $fillable = [
        'assessment_id',
        'name',
        'age',
        'parent_job',
        'domisili',
        'gender',
        'job',
        'institution',
    ];

    // 🔗 relasi
    public function assessment()
    {
        return $this->belongsTo(Assessments::class, 'assessment_id');
    }

    public function details()
    {
        return $this->hasMany(ResponseDetails::class, 'response_id');
    }
}
