<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    // Đảm bảo $fillable có đủ các cột này
    protected $fillable = [
        'post_id',
        'user_id',
        'content',
        'grade',
        'feedback',
        'submitted_at',
        'graded_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
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
    public function files()
    {
        return $this->hasMany(SubmissionFile::class);
    }
}