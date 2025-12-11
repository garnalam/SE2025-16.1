<?php
// app/Models/PollVote.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PollVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'poll_option_id',
        'user_id',
    ];

    /**
    * Lấy lựa chọn mà phiếu bầu này thuộc về.
    */
    public function pollOption(): BelongsTo
    {
        return $this->belongsTo(PollOption::class);
    }

    /**
     * Lấy người dùng đã bầu phiếu này.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}