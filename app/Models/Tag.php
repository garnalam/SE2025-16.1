<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model {
    use HasFactory;
    protected $guarded = [];

    // Thẻ này thuộc về giáo viên nào
    public function user() {
        return $this->belongsTo(User::class);
    }

    // Thẻ này được gắn cho nhiều câu hỏi
    public function questions() {
        return $this->belongsToMany(Question::class, 'question_tag');
    }
}