<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Team as JetstreamTeam;
use Illuminate\Support\Str; // <-- CHỈ CẦN THÊM DÒNG NÀY
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- 1. THÊM DÒNG NÀY
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
        'join_code', // <-- Đã thêm ở bước trước
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

        /**
         * Tự động tạo join_code khi một team mới (lớp học) được tạo.
         */
        static::creating(function ($team) {
            // Chúng ta chỉ tạo mã cho các lớp học (không phải personal team)
            if ($team->personal_team === false) {
                $team->join_code = self::generateUniqueJoinCode();
            }
        });
    }

    /**
     * Tạo một mã join_code duy nhất theo định dạng xxx-yyy-zzz.
     */
    protected static function generateUniqueJoinCode(): string
    {
        do {
            // Tạo mã ngẫu nhiên, chữ thường, 9 ký tự
            $code = sprintf('%s-%s-%s',
                Str::lower(Str::random(3)),
                Str::lower(Str::random(3)),
                Str::lower(Str::random(3))
            );
            // Kiểm tra xem mã đã tồn tại hay chưa, nếu rồi thì lặp lại
        } while (static::where('join_code', $code)->exists());

        return $code;
    }

    public function posts(): HasMany
    {
        // Định nghĩa mối quan hệ "Một Team có nhiều Post"
        return $this->hasMany(Post::class);
    }
}