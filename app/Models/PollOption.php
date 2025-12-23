<?php
// app/Models/PollOption.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PollOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'text',
    ];

    /**
     * Lấy bài đăng (poll) mà lựa chọn này thuộc về.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Lấy tất cả các phiếu bầu (votes) cho lựa chọn này.
     */
    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }
}