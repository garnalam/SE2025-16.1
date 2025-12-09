<?php

namespace App\Services;

use App\Models\User;
use App\Models\Badge;
use App\Notifications\BadgeAwarded;
// Import các huy hiệu
use App\Gamification\Badges\FirstCommentBadge;
use App\Gamification\Badges\ChattyBadge;
use App\Gamification\Badges\PerfectScoreBadge;
class GamificationService
{
    // 1. DANH SÁCH CÁC HUY HIỆU ĐANG HOẠT ĐỘNG
    // (Sau này có huy hiệu mới, bạn chỉ cần thêm class vào đây)
    public static function getBadges(): array
    {
        return [
            FirstCommentBadge::class,
            ChattyBadge::class,
            PerfectScoreBadge::class,
        ];
    }

    /**
     * Hàm kiểm tra tất cả huy hiệu
     * Được gọi mỗi khi user làm hành động gì đó (Comment, Nộp bài...)
     */
    public function checkBadges(User $user, $subject = null)
    {
        // Lấy danh sách class
        $badges = self::getBadges();

        foreach ($badges as $badgeClass) {
            $badgeInstance = new $badgeClass();

            // Kiểm tra điều kiện (qualifier)
            if ($badgeInstance->qualifier($user, $subject)) {
                // Nếu đúng -> Trao huy hiệu theo tên
                $this->awardBadge($user, $badgeInstance->name());
            }
        }
    }

    // --- Các hàm cũ giữ nguyên ---
    
    public function addXp(User $user, int $amount)
    {
        $user->increment('xp', $amount);
        $xpNeeded = $user->level * 100;
        if ($user->xp >= $xpNeeded) {
            $this->levelUp($user);
        }
    }

    protected function levelUp(User $user)
    {
        $user->increment('level');
        $user->xp = 0; 
        $user->save();
    }

    public function awardBadge(User $user, string $badgeName)
    {
        // Tìm huy hiệu trong DB (Lúc này DB đã được đồng bộ bởi Command rồi)
        // Nhưng ta dùng firstOrCreate để an toàn
        $badge = Badge::firstOrCreate(['name' => $badgeName], [
            'description' => 'Huy hiệu tự động',
            'icon_path' => 'badges/default.png'
        ]);

        if (!$user->badges()->where('badge_id', $badge->id)->exists()) {
            $user->badges()->attach($badge->id);
            $user->notify(new BadgeAwarded($badge));
        }
    }
}