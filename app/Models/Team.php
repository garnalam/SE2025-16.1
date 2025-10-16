<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Team as JetstreamTeam;

// ✅ THÊM CÁC DÒNG "USE" NÀY VÀO ĐẦU FILE
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Post;

class Team extends JetstreamTeam
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'personal_team',
    ];

    /**
     * The event map for the model.
     *
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'personal_team' => 'boolean',
    ];
    
    // ✅ THÊM TOÀN BỘ PHƯƠNG THỨC NÀY VÀO
    /**
     * Lấy tất cả các bài đăng của lớp học.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}