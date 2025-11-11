<?php
namespace App\Policies;

use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuizAttemptPolicy
{
    use HandlesAuthorization;

    /**
     * Cho phép xem bài làm (kể cả xem kết quả).
     */
    public function view(User $user, QuizAttempt $attempt): bool
    {
        return $user->id === $attempt->user_id;
    }

    /**
     * Cho phép cập nhật (lưu câu trả lời).
     * Chỉ được phép khi bài làm CHƯA ĐƯỢC NỘP.
     */
    public function update(User $user, QuizAttempt $attempt): bool
    {
        return $user->id === $attempt->user_id && is_null($attempt->completed_at);
    }
}