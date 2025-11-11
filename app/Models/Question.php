<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model {
    use HasFactory;
    protected $guarded = [];

    // Giáo viên sở hữu câu hỏi này
    public function user() {
        return $this->belongsTo(User::class);
    }

    // Các lựa chọn A, B, C, D
    public function options() {
        return $this->hasMany(QuestionOption::class);
    }

    // Các bài quiz đang sử dụng câu hỏi này
    public function posts() {
        return $this->belongsToMany(Post::class, 'post_question');
    }
    public function subject() {
        return $this->belongsTo(Subject::class);
    }
    
    // Câu hỏi này có nhiều thẻ
    public function tags() {
        return $this->belongsToMany(Tag::class, 'question_tag');
    }
}
