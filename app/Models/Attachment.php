<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // THÊM MẢNG NÀY VÀO:
    protected $fillable = [
        'path',
        'original_name',
        'mime_type',
        'size',
        'extracted_content',
    ];

    /**
     * Lấy model cha (Post, Submission, v.v.).
     */
    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}