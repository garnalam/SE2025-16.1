<?php
// app/Http/Controllers/TopicController.php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Topic;
use App\Models\Submission; // <-- THÊM DÒNG NÀY
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

        // 3. LOGIC TẢI DỮ LIỆU
        $topic->load(['posts' => function ($query) {
            $query->with([
                'user', // Tải người đăng
                'pollOptions.votes', // Tải poll
                'parentComments.user', // Tải bình luận gốc + người đăng
                'parentComments.replies.user', // Tải replies + người trả lời
                'attachments' // Tải file đính kèm (cho Bài tập/Tài liệu)
            ])->latest(); // Sắp xếp bài đăng mới nhất lên đầu
        }]);

        
        // === BẮT ĐẦU PHẦN THÊM MỚI ===

        // Lấy $posts từ topic đã load
        $posts = $topic->posts;

        // Xác định quyền (dùng lại logic của bạn)
        $permissions = [
            'canCreatePosts' => $request->user()->belongsToTeam($team),
            'canManageTopics' => Gate::check('update', $topic),
        ];

        // Lấy bài nộp của học sinh
        $userSubmissions = collect(); // Mặc định là rỗng

        // Nếu KHÔNG PHẢI là giáo viên (là học sinh)
        if (!$permissions['canManageTopics']) {
            $userSubmissions = Submission::where('user_id', Auth::id())
                                ->whereIn('post_id', $posts->pluck('id')) // Chỉ lấy bài nộp cho các post trong topic này
                                ->with('files') // Lấy cả file đã nộp (Submission->files())
                                ->get()
                                ->keyBy('post_id'); // Key theo post_id để Vue tìm cho nhanh
        }
        
        // === KẾT THÚC PHẦN THÊM MỚI ===


        // 4. Trả về trang Vue (ĐÃ CẬP NHẬT)
        return Inertia::render('Topics/Show', [
            'team' => $team,
            'topic' => $topic,
            'posts' => $posts, // Truyền posts đã load
            'authUserId' => Auth::id(),
            'permissions' => $permissions, // Truyền mảng permissions
            'userSubmissions' => $userSubmissions, // <-- TRUYỀN PROP MỚI
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