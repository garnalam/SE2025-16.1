<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model {
    use HasFactory;
    protected $guarded = [];

    // Môn học này thuộc về giáo viên nào
    public function user() {
        return $this->belongsTo(User::class);
    }

    // Môn học này có nhiều câu hỏi
    public function questions() {
        return $this->hasMany(Question::class);
    }
}