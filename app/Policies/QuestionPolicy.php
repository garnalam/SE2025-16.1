<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuestionPolicy
{
    use HandlesAuthorization;

    /**
     * Quyền xem danh sách câu hỏi (trang /questions).
     * Chỉ 'teacher' mới được xem.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'teacher';
    }

    /**
     * Quyền xem một câu hỏi cụ thể.
     * (Chúng ta sẽ cho phép nếu họ là 'teacher' và là chủ sở hữu).
     */
    public function view(User $user, Question $question): bool
    {
        return $user->role === 'teacher' && $user->id === $question->user_id;
    }

    /**
     * Quyền tạo câu hỏi mới.
     * Chỉ 'teacher' mới được tạo.
     */
    public function create(User $user): bool
    {
        return $user->role === 'teacher';
    }

    /**
     * Quyền cập nhật câu hỏi. (Bạn đã có)
     * Chỉ chủ sở hữu mới được cập nhật.
     */
    public function update(User $user, Question $question): bool
    {
        return $user->id === $question->user_id;
    }

    /**
     * Quyền xóa câu hỏi. (Bạn đã có)
     * Chỉ chủ sở hữu mới được xóa.
     */
    public function delete(User $user, Question $question): bool
    {
        return $user->id === $question->user_id;
    }

    // (Bạn có thể thêm các hàm restore/forceDelete nếu cần)
    public function restore(User $user, Question $question): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Question $question): bool
    {
        return false;
    }
}
