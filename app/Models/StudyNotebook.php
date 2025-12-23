<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudyNotebook extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id', 
        'user_id', 
        'title', 
        'content', 
        'type' // 'notebook' hoặc 'spreadsheet'
    ];
}