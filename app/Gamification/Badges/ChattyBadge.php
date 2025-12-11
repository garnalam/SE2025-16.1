<?php

namespace App\Gamification\Badges;

use App\Models\User;

class ChattyBadge implements BadgeTypeInterface
{
    public function name(): string
    {
        return 'Thánh chém gió';
    }

    public function description(): string
    {
        return 'Đạt mốc 5 bình luận thảo luận';
    }

    public function icon(): string
    {
        return 'badges/thanh-chem-gio.png';
    }

    public function qualifier(User $user, $subject = null): bool
    {
        return $user->comments()->count() >= 5;
    }
}