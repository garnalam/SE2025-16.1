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
use App\Models\QuizAttempt;


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

    // 1. Tải bài đăng và các quan hệ
    $topic->load(['posts' => function ($query) {
        $query->with([
            'user', 
            'pollOptions.votes', 
            'parentComments.user', 
            'parentComments.replies.user', 
            'attachments'
        ])->latest();
    }]);

    $posts = $topic->posts;

    // 2. Xác định quyền
    $permissions = [
        'canCreatePosts' => $request->user()->belongsToTeam($team),
        'canManageTopics' => Gate::check('update', $topic),
    ];

    // 3. Lấy dữ liệu bài nộp của học sinh (SỬA Ở ĐÂY)
    $userSubmissions = collect(); 
    $userQuizAttempts = collect();

    // Chỉ lấy nếu không phải là giáo viên quản lý (tức là học sinh)
    // Hoặc bỏ check này nếu muốn giáo viên cũng thấy bài test của mình
    if (Auth::check()) { 
        
        // Lấy Assignment Submissions (Key by post_id)
        // 👇 ĐOẠN CODE NÀY QUAN TRỌNG ĐỂ HIỆN ĐIỂM
        $userSubmissions = Submission::whereIn('post_id', $posts->pluck('id'))
            ->where('user_id', Auth::id())
            ->with('files') // Load file đính kèm để hiển thị lại
            ->get()
            ->keyBy('post_id');

        // Lấy Quiz Attempts (Key by post_id)
        $userQuizAttempts = QuizAttempt::whereIn('post_id', $posts->pluck('id'))
            ->where('user_id', Auth::id())
            ->whereNotNull('completed_at')
            ->get()
            ->keyBy('post_id');
    }
    
    // 4. Gán quyền cho từng Post (Giữ nguyên logic của bạn)
    $postsWithPermissions = $posts->map(function ($post) use ($request) {
        $postArray = $post->toArray(); 
        $postArray['can'] = [
            'update' => $request->user()->can('update', $post),
            'delete' => $request->user()->can('delete', $post),
        ];
        $postArray['created_at_formatted'] = $post->created_at->diffForHumans();
        return $postArray;
    });

    // 5. Trả về View
    return Inertia::render('Topics/Show', [
        'team' => $team,
        'topic' => $topic,
        'posts' => $postsWithPermissions,
        'authUserId' => Auth::id(),
        'permissions' => $permissions, 
        
        // Truyền xuống Vue
        'userSubmissions' => $userSubmissions, 
        'userQuizAttempts' => $userQuizAttempts,
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