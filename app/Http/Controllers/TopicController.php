<?php
// app/Http/Controllers/TopicController.php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TopicController extends Controller
{
    /**
     * Tạo một chủ đề mới.
     */
    public function store(Request $request, Team $team)
    {
        // 1. Phân quyền: Kiểm tra xem user có phải là 'teacher' của team này không
        Gate::authorize('create', [Topic::class, $team]);

        // 2. Validate input
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        // 3. Tạo chủ đề (liên kết với team và user)
        $team->topics()->create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);

        return back(303);
    }

    /**
     * Cập nhật chủ đề
     */
    public function update(Request $request, Topic $topic)
    {
        // 1. Phân quyền
        Gate::authorize('update', $topic);

        // 2. Validate
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
        
        // 3. Cập nhật
        $topic->update($validated);

        return back(303);
    }

    /**
     * Xóa một chủ đề.
     */
    public function destroy(Request $request, Topic $topic)
    {
        // 1. Phân quyền: Kiểm tra user có quyền xóa chủ đề này không
        Gate::authorize('delete', $topic);

        // 2. Xóa
        $topic->delete();

        return back(303);
    }

    /**
     * Hiển thị một chủ đề cụ thể và các bài đăng bên trong nó.
     */
    public function show(Request $request, Topic $topic)
    {
        $team = $topic->team;
        if (Gate::denies('view', $team)) {
            abort(403);
        }

        // 3. LOGIC TẢI DỮ LIỆU (Giữ nguyên)
        $topic->load(['posts' => function ($query) {
            $query->with([
                'user', // Tải người đăng
                'pollOptions.votes', // Tải poll
                
                // (MỚI) Tải bình luận
                'parentComments.user', // Tải bình luận gốc + người đăng
                'parentComments.replies.user', // Tải replies + người trả lời
                
            ])->latest();
        }]);

        // 4. Trả về trang Vue
        return Inertia::render('Topics/Show', [
            'team' => $team,
            'topic' => $topic,
            
            // ===== ĐÃ SỬA LỖI =====
            // Chỉ cần truyền $topic->posts
            // Inertia sẽ tự động chuyển 'parentComments' thành 'parent_comments' (snake_case)
            // khi gửi sang Vue.
            'posts' => $topic->posts,
            // ======================

            'authUserId' => Auth::id(),
            'permissions' => [
                // SỬA DÒNG NÀY:
                'canCreatePosts' => $request->user()->belongsToTeam($team),
                // DÒNG BÊN DƯỚI GIỮ NGUYÊN:
                'canManageTopics' => Gate::check('update', $topic),
            ]
        ]);
    }

    /**
     * Khóa hoặc mở khóa một chủ đề.
     */
    public function toggleLock(Request $request, Topic $topic)
    {
        // 1. Phân quyền
        Gate::authorize('toggleLock', $topic);

        // 2. Đảo ngược trạng thái 'is_locked'
        $topic->update([
            'is_locked' => ! $topic->is_locked,
        ]);

        return back(303);
    }
}