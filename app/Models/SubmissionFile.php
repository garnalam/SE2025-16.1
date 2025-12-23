<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmissionFile extends Model
{
    use HasFactory;

    protected $fillable = ['submission_id', 'file_path', 'original_name'];

    // File này thuộc về 1 bài nộp
    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }
    
}