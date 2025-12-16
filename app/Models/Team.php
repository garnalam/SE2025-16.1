<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Team as JetstreamTeam;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // <--- 1. THÊM DÒNG NÀY
use Laravel\Jetstream\Jetstream; // <--- 2. THÊM DÒNG NÀY
use App\Models\AttendanceSession;
class Team extends JetstreamTeam
{
    use HasFactory;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'personal_team' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'personal_team',
        'join_code',
        'late_policy_type',     // 'none', 'fixed', 'daily'
        'late_penalty_percent', // 10, 30...
        'weight_attendance',
        'weight_quiz',
        'weight_assignment',
    ];

    /**
     * The event map for the model.
     *
     * @var array<class-string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];

    /**
     * Khởi động các model event.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($team) {
            if ($team->personal_team === false) {
                $team->join_code = self::generateUniqueJoinCode();
            }
        });
    }

    protected static function generateUniqueJoinCode(): string
    {
        do {
            $code = sprintf('%s-%s-%s',
                Str::lower(Str::random(3)),
                Str::lower(Str::random(3)),
                Str::lower(Str::random(3))
            );
        } while (static::where('join_code', $code)->exists());

        return $code;
    }

    // --- 👇 ĐÂY LÀ PHẦN QUAN TRỌNG BẠN ĐANG THIẾU 👇 ---
    
    /**
     * Ghi đè quan hệ users để lấy thêm cột 'role'
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(Jetstream::userModel(), Jetstream::membershipModel())
                    ->withPivot('role') // Quan trọng nhất: Lấy cột role từ bảng trung gian
                    ->withTimestamps()
                    ->as('membership');
    }

    // ---------------------------------------------------

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class);
    }

    public function attendanceSessions()
{
    return $this->hasMany(AttendanceSession::class);
}
}