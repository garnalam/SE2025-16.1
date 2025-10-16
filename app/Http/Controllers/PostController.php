<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    /**
     * Lưu một bài đăng mới.
     */
    public function store(Request $request, Team $team)
    {
        // 1. Kiểm tra quyền: Chỉ chủ sở hữu lớp học (Giáo viên) mới được đăng bài
        Gate::authorize('addTeamMember', $team);

        // 2. Validate dữ liệu gửi lên
        $request->validate([
            'content' => ['required', 'string'],
        ]);

        // 3. Tạo bài đăng
        $team->posts()->create([
            'user_id' => $request->user()->id,
            'content' => $request->input('content'),
        ]);

        // 4. Quay lại trang trước đó với thông báo thành công
        return back()->with('success', 'Đã đăng bài thành công!');
    }
}