<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flashcard extends Model
{
    use HasFactory;
    protected $fillable = ['flashcard_set_id', 'front_content', 'back_content', 'image_url', 'status'];
}
