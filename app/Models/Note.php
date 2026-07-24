<?php

namespace App\Models;

use Carbon\Traits\Timestamp;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = ['user_id', 'book_code', 'book_name', 'chapter', 'verse', 'body'];

    use Timestamp;
}