<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiSession extends Model
{
    use HasFactory;

    // 👇 DÒNG NÀY RẤT QUAN TRỌNG: Cho phép lưu tất cả các trường
    protected $guarded = []; 

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(AiMessage::class)->orderBy('created_at', 'asc');
    }
}