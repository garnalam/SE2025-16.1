<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlashcardSet extends Model
{
    use HasFactory;
    protected $fillable = ['team_id', 'user_id', 'title', 'description', 'color'];

    public function cards() {
        return $this->hasMany(Flashcard::class);
    }
}