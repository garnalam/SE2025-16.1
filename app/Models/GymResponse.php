<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GymResponse extends Model
{
    protected $guarded = [];

    // --- Mối quan hệ với Câu hỏi (Đã có) ---
    public function question() {
        return $this->belongsTo(Question::class); 
    }

    // --- [BỔ SUNG QUAN TRỌNG] Mối quan hệ với Phiên tập ---
    // Hàm này giúp Controller trỏ từ câu trả lời ngược về phiên tập để lấy user_id
    public function session() {
        // Vì tên bảng là gym_sessions và khóa ngoại là gym_session_id
        return $this->belongsTo(GymSession::class, 'gym_session_id');
    }
}