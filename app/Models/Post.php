<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- THÊM DÒNG NÀY
class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'team_id',
        'user_id',
        'topic_id',
        'post_type', // <-- THÊM DÒNG NÀY
        'are_comments_enabled', // <-- THÊM DÒNG NÀY
    ];

    protected $casts = [
        'are_comments_enabled' => 'boolean', // <-- THÊM DÒNG NÀY
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

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }
    public function pollOptions(): HasMany
    {
        return $this->hasMany(PollOption::class);
    }
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Lấy chỉ các bình luận GỐC (parent_id = null).
     */
    public function parentComments(): HasMany
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id');
    }
}
