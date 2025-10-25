<?php
// app/Http/Controllers/CommentController.php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException; // <-- Quan trọng

class CommentController extends Controller
{
    /**
     * Lưu một bình luận (hoặc trả lời) mới.
     */
    public function store(Request $request, Post $post)
    {
        // 1. Lấy team và topic từ post
        $team = $post->team;
        $topic = $post->topic;

        // 2. PHÂN QUYỀN (Theo yêu cầu của bạn)

        // 2a. Phải là thành viên của lớp
                if (! $request->user()->belongsToTeam($team)) {
            abort(403, 'Bạn không phải là thành viên của lớp học này.');
        }

        // 2b. Bài đăng phải cho phép bình luận
        if (! $post->are_comments_enabled) {
            throw ValidationException::withMessages([
               'body' => 'Bài đăng này đã tắt tính năng bình luận.',
            ]);
        }

        // 2c. (QUAN TRỌNG) Nếu chủ đề bị khóa (is_locked)
        if ($topic->is_locked) {
            // Chỉ 'teacher' hoặc 'admin' mới được bình luận
            if ($request->user()->role === 'student') {
                throw ValidationException::withMessages([
                   'body' => 'Chủ đề này đã bị khóa. Chỉ giáo viên mới có thể bình luận.',
                ]);
            }
        }
        
        // 3. Validate input
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'parent_id' => ['nullable', 'exists:comments,id'], // Dùng cho replies
        ]);

        // 4. Tạo bình luận
        $post->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        return back(303);
    }
}