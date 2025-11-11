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
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- THÊM DÒNG NÀY
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
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
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
}
