<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periods extends Model
{
   protected $fillable = [
        'name',
        'start_date',
        'end_date',
    ];

    public function assessments()
    {
        return $this->hasMany(Assessments::class);
    }
}
