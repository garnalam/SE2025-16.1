<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAnswer extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function quizAttempt() {
        return $this->belongsTo(QuizAttempt::class);
    }

    public function question() {
        return $this->belongsTo(Question::class);
    }

    public function option() {
        // Đảm bảo bạn đã thêm quan hệ này nếu cần
        return $this->belongsTo(QuestionOption::class, 'question_option_id');
    }
}