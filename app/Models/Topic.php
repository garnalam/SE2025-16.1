<?php
// app/Models/Topic.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Topic extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'team_id',
        'user_id',
        'name',
        'description',
        'is_locked', // <-- Đã có trong $fillable
    ];

    /**
     * The attributes that should be cast.
     * (Đây là phần quan trọng nhất)
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_locked' => 'boolean', // <-- HÃY THÊM DÒNG NÀY
    ];

    /**
     * Lấy lớp học (team) mà chủ đề này thuộc về.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Lấy người dùng (giáo viên) đã tạo chủ đề này.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Lấy tất cả các bài đăng (posts) thuộc chủ đề này.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}