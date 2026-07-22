<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BibleProfile extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['id', 'subject', 'book', 'chapter', 'verse', 'end_verse', 'content'];

    protected $casts = [
        'content' => 'array',
    ];
}