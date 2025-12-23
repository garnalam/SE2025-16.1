<?php

namespace App\Gamification\Badges;

use App\Models\User;

interface BadgeTypeInterface
{
    /**
     * Tên huy hiệu (Dùng làm định danh trong DB)
     */
    public function name(): string;

    /**
     * Mô tả huy hiệu
     */
    public function description(): string;

    /**
     * Đường dẫn icon (tương đối từ public/storage)
     */
    public function icon(): string;

    /**
     * Logic kiểm tra: Trả về true nếu user xứng đáng nhận
     * $subject: Đối tượng liên quan (Comment, Submission...)
     */
    public function qualifier(User $user, $subject = null): bool;
}