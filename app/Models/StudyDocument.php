<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudyDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'team_id', // ID lớp học
        'title',
        'file_path',
        'extracted_content', // Cột cũ (cho AI)
        'file_type',         // Cột mới (pdf, img...)
        'is_teacher_resource' // Cột mới
    ];

    // Quan hệ: Một tài liệu có nhiều vết vẽ (Annotations)
    public function annotations()
    {
        return $this->hasMany(DocumentAnnotation::class, 'study_document_id');
    }

    // Quan hệ: Thuộc về người dùng nào
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}