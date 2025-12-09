<?php

namespace App\Gamification\Badges;

use App\Models\User;

class PerfectScoreBadge implements BadgeTypeInterface
{
    public function name(): string
    {
        return 'Học bá đỉnh cao';
    }

    public function description(): string
    {
        return 'Lần đầu tiên đạt điểm tối đa trong bài tập';
    }

    public function icon(): string
    {
        return 'badges/perfect-score.png'; 
    }

    public function qualifier(User $user, $subject = null): bool
    {
        // Logic: Kiểm tra xem user có bài nộp nào (submission) điểm bằng max_points không
        // Chúng ta join bảng submissions với posts để so sánh điểm đạt được vs điểm tối đa
        return $user->submissions()
            ->join('posts', 'submissions.post_id', '=', 'posts.id')
            ->whereColumn('submissions.grade', '>=', 'posts.max_points')
            ->exists();
    }
}