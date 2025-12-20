<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentAnnotation extends Model
{
    use HasFactory;

    protected $fillable = ['study_document_id', 'user_id', 'data', 'page_number'];

    // Tự động chuyển JSON string trong DB thành Array/Object khi lấy ra
    protected $casts = [
        'data' => 'array',
    ];
}