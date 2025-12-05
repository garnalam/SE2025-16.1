<?php
// app/Http/Controllers/CommentController.php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException; // <-- Quan trọng
use App\Notifications\NewCommentNotification;
use Illuminate\Support\Facades\Auth;
use App\Notifications\ReplyCommentNotification;
use App\Models\Comment; // Nếu cần tìm comment cha
class CommentController extends Controller
{
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

        // 4. TẠO BÌNH LUẬN (Gán vào biến $comment)
        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        // =========================================================
        // 5. XỬ LÝ THÔNG BÁO (NOTIFICATION)
        // =========================================================
        
        $currentUser = $request->user();

        // -----------------------------------------------------
        // A. Thông báo cho CHỦ BÀI VIẾT (NewCommentNotification)
        // -----------------------------------------------------
        $postOwner = $post->user;

        // Chỉ gửi nếu người comment KHÔNG PHẢI là chủ bài viết
        if ($postOwner->id !== $currentUser->id) {
            $postOwner->notify(new NewCommentNotification($comment));
        }

        // -----------------------------------------------------
        // B. Thông báo cho NGƯỜI ĐƯỢC TRẢ LỜI (ReplyCommentNotification)
        // -----------------------------------------------------
        if ($comment->parent_id) {
            // Tìm comment cha để lấy người sở hữu
            $parentComment = Comment::find($comment->parent_id);

            if ($parentComment) {
                $parentOwner = $parentComment->user;

                // Điều kiện gửi thông báo trả lời:
                // 1. Không tự trả lời chính mình ($parentOwner->id !== $currentUser->id)
                // 2. (Tùy chọn) Nếu người được trả lời ($parentOwner) chính là chủ bài viết ($postOwner), 
                //    họ đã nhận thông báo A ở trên rồi. Tuy nhiên, thông báo "Trả lời" chi tiết hơn,
                //    nên ta vẫn gửi cả 2 hoặc chặn bớt tùy bạn. Ở đây tôi để code gửi cả 2 cho chắc.
                
                if ($parentOwner->id !== $currentUser->id) {
                    $parentOwner->notify(new ReplyCommentNotification($comment));
                }
            }
        }

        return back(303);
    }
}