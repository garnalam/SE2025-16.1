<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'team_id',
        'user_id',
    ];

    /**
     * Lấy thông tin người dùng đã tạo bài đăng.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Lấy thông tin lớp học của bài đăng.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
