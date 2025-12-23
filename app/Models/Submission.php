<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'content',
        'grade',
        'feedback',
        'submitted_at',
        'graded_at',
        // 👇 BỔ SUNG 2 CỘT NÀY ĐỂ LƯU KẾT QUẢ TỪ AI
        'ai_suggested_grade',
        'ai_suggested_feedback',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        // 'ai_suggested_grade' => 'float', // Có thể thêm nếu muốn ép kiểu
    ];

    // Bài nộp này thuộc về 1 bài post (bài tập)
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    // Bài nộp này thuộc về 1 user (học sinh)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Bài nộp này có nhiều file
    // ✅ Tên hàm này là 'files', nên trong Job bắt buộc phải dùng $submission->files
    public function files()
    {
        return $this->hasMany(SubmissionFile::class);
    }
}