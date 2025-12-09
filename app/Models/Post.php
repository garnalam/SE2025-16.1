<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany; // <-- THÊM DÒNG NÀY

class Post extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'content',
        'team_id',
        'user_id',
        'topic_id',
        'post_type',
        'are_comments_enabled',
        'is_proctored',
        
        // --- THÊM CÁC TRƯỜNG CHO ASSIGNMENT ---
        'title',
        'due_date',
        'max_points',
        // --- KẾT THÚC THÊM ---
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'are_comments_enabled' => 'boolean',
        
        // --- THÊM CASTS CHO ASSIGNMENT ---
        'due_date' => 'datetime',
        // --- KẾT THÚC THÊM ---
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

    /**
     * Lấy thông tin chủ đề của bài đăng.
     */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    /**
     * Lấy các lựa chọn bình chọn (nếu là poll).
     */
    public function pollOptions(): HasMany
    {
        return $this->hasMany(PollOption::class);
    }

    /**
     * Lấy tất cả bình luận.
     */
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

    /**
     * Lấy tất cả các file đính kèm cho bài đăng.
     * (Dùng cho cả Material và Assignment)
     */
    public function attachments(): MorphMany // <-- THÊM HÀM NÀY
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }
    // Các câu hỏi có trong bài quiz này
    public function questions() {
        return $this->belongsToMany(Question::class, 'post_question');
    }

    // Các lần làm bài của học sinh cho bài quiz này
    public function attempts() {
        return $this->hasMany(QuizAttempt::class, 'post_id');
    }
    public function assignedUsers() {
        return $this->belongsToMany(User::class, 'post_user');
    }
    
}