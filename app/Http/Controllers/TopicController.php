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

        // 3. LOGIC TẢI DỮ LIỆU (Giữ nguyên của bạn)
        $topic->load(['posts' => function ($query) {
            $query->with([
                'user', // Tải người đăng
                'pollOptions.votes', // Tải poll
                'parentComments.user', // Tải bình luận gốc + người đăng
                'parentComments.replies.user', // Tải replies + người trả lời
                'attachments' // Tải file đính kèm
            ])->latest(); // Sắp xếp bài đăng mới nhất lên đầu
        }]);

        
        // Lấy $posts từ topic đã load
        $posts = $topic->posts;

        // Xác định quyền (dùng lại logic của bạn)
        $permissions = [
            'canCreatePosts' => $request->user()->belongsToTeam($team),
            'canManageTopics' => Gate::check('update', $topic),
        ];

        // Lấy bài nộp của học sinh (Giữ nguyên của bạn)
        $userSubmissions = collect(); 
        if (!$permissions['canManageTopics']) {
            $userSubmissions = Submission::where('user_id', Auth::id())
                                ->whereIn('post_id', $posts->pluck('id')) 
                                ->with('files') 
                                ->get()
                                ->keyBy('post_id'); 
        }
        
        // === THÊM MỚI: GÁN QUYỀN SỬA/XÓA CHO TỪNG BÀI POST ===
        // (Đây là phần bị thiếu khiến nút Sửa/Xóa bị lỗi)
        $postsWithPermissions = $posts->map(function ($post) use ($request) {
            // Chuyển đổi post thành mảng để thêm key mới
            $postArray = $post->toArray(); 
            
            // Thêm key 'can' (quyền)
            $postArray['can'] = [
                'update' => $request->user()->can('update', $post),
                'delete' => $request->user()->can('delete', $post),
            ];
            
            // Thêm key 'created_at_formatted' (định dạng thời gian)
            $postArray['created_at_formatted'] = $post->created_at->diffForHumans();

            return $postArray;
        });
        // === KẾT THÚC PHẦN THÊM MỚI ===


        // 4. Trả về trang Vue (ĐÃ CẬP NHẬT)
        return Inertia::render('Topics/Show', [
            'team' => $team,
            'topic' => $topic,
            'posts' => $postsWithPermissions, // <-- SỬ DỤNG $postsWithPermissions
            'authUserId' => Auth::id(),
            'permissions' => $permissions, 
            'userSubmissions' => $userSubmissions, 
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