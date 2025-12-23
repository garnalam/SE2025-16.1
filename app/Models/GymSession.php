<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GymSession extends Model
{
    protected $guarded = [];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function responses() {
        return $this->hasMany(GymResponse::class);
    }
}