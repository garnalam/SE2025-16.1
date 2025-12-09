<?php

namespace App\Gamification\Badges;

use App\Models\User;

class FirstCommentBadge implements BadgeTypeInterface
{
    public function name(): string
    {
        return 'Bình luận viên tập sự';
    }

    public function description(): string
    {
        return 'Đăng bình luận đầu tiên trong lớp học';
    }

    public function icon(): string
    {
        // Bạn nhớ copy file ảnh vào: public/storage/badges/first-comment.png
        return 'badges/first-comment.png';
    }

    public function qualifier(User $user, $subject = null): bool
    {
        // Logic: Chỉ trao khi tổng số comment đúng bằng 1
        return $user->comments()->count() === 1;
    }
}