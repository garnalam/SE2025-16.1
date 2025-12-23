<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiMessage extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function session()
    {
        return $this->belongsTo(AiSession::class, 'ai_session_id');
    }
}