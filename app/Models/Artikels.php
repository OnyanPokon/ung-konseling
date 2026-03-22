<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Artikels extends Model
{
    protected $table = 'artikels';

    protected $fillable = [
        'judul',
        'slug',
        'konten',
        'thumbnail',
        'status',
    ];
}
