<?php
// app/Http/Controllers/CommentController.php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Notifications\NewCommentNotification;
use App\Notifications\ReplyCommentNotification;
use App\Models\Comment;
use App\Services\GamificationService; // Import Service

class CommentController extends Controller
{
    protected $gamification;

    // Inject GamificationService vào Constructor
    public function __construct(GamificationService $gamification)
    {
        $this->gamification = $gamification;
    }

    /**
     * Lưu một bình luận (hoặc trả lời) mới.
     */
    public function store(Request $request, Post $post)
    {
        // 1. Lấy team và topic
        $team = $post->team;
        $topic = $post->topic;

        // 2. PHÂN QUYỀN
        if (! $request->user()->belongsToTeam($team)) {
            abort(403, 'Bạn không phải là thành viên của lớp học này.');
        }

        if (! $post->are_comments_enabled) {
            throw ValidationException::withMessages([
               'body' => 'Bài đăng này đã tắt tính năng bình luận.',
            ]);
        }

        if ($topic->is_locked) {
            if ($request->user()->role === 'student') {
                throw ValidationException::withMessages([
                   'body' => 'Chủ đề này đã bị khóa. Chỉ giáo viên mới có thể bình luận.',
                ]);
            }
        }
        
        // 3. Validate
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'parent_id' => ['nullable', 'exists:comments,id'],
        ]);

        // 4. TẠO BÌNH LUẬN
        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        // =========================================================
        // [MỚI] GAMIFICATION LOGIC (CHUẨN CODE-FIRST)
        // =========================================================
        if ($request->user()->role === 'student') {
            
            // 1. Cộng 10 XP
            $this->gamification->addXp($request->user(), 10);

            // 2. Tự động kiểm tra tất cả huy hiệu
            // Hàm này sẽ chạy qua danh sách Badge Classes (FirstCommentBadge, ChattyBadge...)
            // Bạn không cần viết if/else thủ công ở đây nữa.
            $this->gamification->checkBadges($request->user(), $comment);
        }
        // =========================================================

        // =========================================================
        // 5. XỬ LÝ THÔNG BÁO (NOTIFICATION - Cho tác giả bài viết)
        // =========================================================
        
        $currentUser = $request->user();

        // A. Thông báo cho CHỦ BÀI VIẾT
        $postOwner = $post->user;
        if ($postOwner->id !== $currentUser->id) {
            $postOwner->notify(new NewCommentNotification($comment));
        }

        // B. Thông báo cho NGƯỜI ĐƯỢC TRẢ LỜI
        if ($comment->parent_id) {
            $parentComment = Comment::find($comment->parent_id);
            if ($parentComment) {
                $parentOwner = $parentComment->user;
                if ($parentOwner->id !== $currentUser->id) {
                    $parentOwner->notify(new ReplyCommentNotification($comment));
                }
            }
        }

        return back(303);
    }
}