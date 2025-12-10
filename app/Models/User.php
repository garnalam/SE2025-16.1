<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // <-- [QUAN TRỌNG] THÊM DÒNG NÀY
use App\Models\AttendanceRecord;
class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * Tự động load danh sách badges mỗi khi truy vấn User.
     * Điều này giúp hiển thị huy hiệu ở trang Profile mà không cần query thêm.
     */
    protected $with = ['badges']; // <-- [QUAN TRỌNG] THÊM DÒNG NÀY

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'xp',     // Gamification: Điểm kinh nghiệm
        'level',  // Gamification: Cấp độ
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // --- CÁC QUAN HỆ CŨ ---

    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class);
    }

    public function pollVotes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
    
    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }
    
    // Ngân hàng câu hỏi của giáo viên
    public function questions() {
        return $this->hasMany(Question::class);
    }

    // Các lần làm bài của học sinh
    public function quizAttempts() {
        return $this->hasMany(QuizAttempt::class);
    }
    
    public function subjects() {
        return $this->hasMany(Subject::class);
    }

    // Các thẻ của giáo viên này
    public function tags() {
        return $this->hasMany(Tag::class);
    }
    
    // Các mẫu quiz của giáo viên
    public function quizTemplates() {
        return $this->hasMany(QuizTemplate::class);
    }
    
    // Các bài quiz được giao cho học sinh
    public function assignedPosts() {
        return $this->belongsToMany(Post::class, 'post_user');
    }

    // --- CÁC QUAN HỆ GAMIFICATION (MỚI) ---

    // Quan hệ với Badge (Nhiều - Nhiều)
    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class)->withPivot('awarded_at');
    }

    // Tính điểm XP cần thiết để lên cấp tiếp theo
    // Ví dụ: Level 1 cần 100 XP để lên Level 2.
    public function getNextLevelXpAttribute()
    {
        return $this->level * 100; 
    }
    
    // Tính % kinh nghiệm hiện có so với cấp tiếp theo (để hiển thị thanh progress bar)
    public function getXpProgressAttribute()
    {
        $target = $this->next_level_xp;
        // Tránh chia cho 0
        if ($target <= 0) return 0;
        
        return round(($this->xp / $target) * 100);
    }

    public function attendanceRecords()
{
    return $this->hasMany(AttendanceRecord::class);
}
}