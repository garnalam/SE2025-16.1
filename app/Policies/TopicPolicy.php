<?php
// app/Policies/TopicPolicy.php

namespace App\Policies;

use App\Models\Team;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TopicPolicy
{
    use HandlesAuthorization;

    /**
     * Quyền tạo chủ đề mới.
     */
    public function create(User $user, Team $team): bool
    {
        // Chỉ chủ sở hữu của team (hoặc user có role 'teacher') mới được tạo
        return $user->ownsTeam($team) || $user->role === 'teacher';
    }

    /**
     * Quyền cập nhật chủ đề.
     */
    public function update(User $user, Topic $topic): bool
    {
        // Chỉ chủ sở hữu của team mà chủ đề đó thuộc về mới được sửa
        return $user->ownsTeam($topic->team) || $user->role === 'teacher';
    }

    /**
     * Quyền xóa chủ đề.
     */
    public function delete(User $user, Topic $topic): bool
    {
        // Tương tự như update
        return $user->ownsTeam($topic->team) || $user->role === 'teacher';
    }

    public function toggleLock(User $user, Topic $topic): bool
    {
        // Chỉ chủ sở hữu/GV của team mới được khóa
        // (Bạn có thể dùng 'update' vì logic là như nhau)
        return $user->ownsTeam($topic->team) || $user->role === 'teacher';
    }
}