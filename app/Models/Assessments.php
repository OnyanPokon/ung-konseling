<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class Assessments extends Model
{
    protected $fillable = [
        'period_id',
        'title',
        'description',
        'slug',
        'is_published',
    ];

    public function period()
    {
        return $this->belongsTo(Periods::class);
    }

    public function questions()
    {
        return $this->hasMany(Questions::class, 'assessment_id');
    }

    public function responses()
    {
        return $this->hasMany(Responses::class, 'assessment_id');
    }

    protected static function booted()
    {
        static::creating(function ($assessment) {
            if (!$assessment->slug) {
                $assessment->slug = Str::slug($assessment->title) . '-' . uniqid();
            }
        });
    }
}
