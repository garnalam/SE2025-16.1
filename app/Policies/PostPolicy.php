<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PostPolicy
{
    /**
     * Xác định xem người dùng có thể xem bất kỳ bài đăng nào không.
     */
    public function viewAny(User $user): bool
    {
        // Ai cũng có thể xem
        return true;
    }

    /**
     * Xác định xem người dùng có thể xem bài đăng cụ thể không.
     */
    public function view(User $user, Post $post): bool
    {
        // Logic đơn giản: Nếu bạn ở trong nhóm (lớp học) đó, bạn có thể xem.
        return $user->belongsToTeam($post->team);
    }

    /**
     * Xác định xem người dùng có thể tạo bài đăng không.
     */
    public function create(User $user): bool
    {
        // Bất kỳ ai đã đăng nhập đều có thể tạo (có thể bạn muốn giới hạn chỉ 'teacher'?)
        return true;
    }

    /**
     * Xác định xem người dùng có thể cập nhật (sửa) bài đăng không.
     * Đây là hàm quan trọng cho vấn đề của bạn.
     */
    public function update(User $user, Post $post): bool
    {
        // 1. Cho phép nếu người dùng là 'teacher' (từ bảng users)
        if ($user->role === 'teacher') {
            return true;
        }

        // 2. Hoặc, cho phép nếu người dùng là tác giả gốc của bài đăng
        return $user->id === $post->user_id;
    }


    /**
     * Xác định xem người dùng có thể xóa bài đăng không.
     * Đây là hàm quan trọng cho vấn đề của bạn.
     */
    public function delete(User $user, Post $post): bool
    {
        // 1. Cho phép nếu người dùng là 'teacher' (từ bảng users)
        if ($user->role === 'teacher') {
            return true;
        }

        // 2. Hoặc, cho phép nếu người dùng là tác giả gốc của bài đăng
        return $user->id === $post->user_id;
    }

    /**
     * Xác định xem người dùng có thể khôi phục bài đăng không.
     */
    public function restore(User $user, Post $post): bool
    {
        // Chỉ giáo viên
        return $user->role === 'teacher';
    }

    /**
     * Xác định xem người dùng có thể xóa vĩnh viễn bài đăng không.
     */
    public function forceDelete(User $user, Post $post): bool
    {
         // Chỉ giáo viên
        return $user->role === 'teacher';
    }
}
