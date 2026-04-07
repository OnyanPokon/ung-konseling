<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Screenings extends Model
{
    protected $fillable = [
        'title',
        'description',
        'slug',
        'is_published',
    ];

    public function questions()
    {
        return $this->hasMany(QuestionScreenings::class, 'screening_id');
    }

    public function responses()
    {
        return $this->hasMany(ScreeningResponses::class, 'screening_id');
    }

    protected static function booted()
    {
        static::creating(function ($screening) {
            if (!$screening->slug) {
                $screening->slug = Str::slug($screening->title) . '-' . uniqid();
            }
        });
    }
}
