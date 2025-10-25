<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB; // <-- THÊM DÒNG NÀY
use Illuminate\Validation\Rule; // <-- THÊM DÒNG NÀY
use App\Models\Post; // <-- THÊM DÒNG NÀY NẾU CHƯA CÓ
use Illuminate\Support\Facades\Auth; // <-- THÊM DÒNG NÀY NẾU CHƯA CÓ
class PostController extends Controller
{
    /**
     * Lưu một bài đăng mới (Text hoặc Poll) vào chủ đề.
     */
    public function store(Request $request, Topic $topic)
    {
        $team = $topic->team;

        // 1. Phân quyền chung: Phải là thành viên
        if (! $request->user()->belongsToTeam($team)) {
            abort(403, 'Bạn không phải là thành viên của lớp học này.');
        }

        // 2. Validate dữ liệu (ĐÃ CẬP NHẬT)
        $validated = $request->validate([
            // 'content' bây giờ là câu hỏi của Poll, hoặc nội dung bài text
            'content' => ['required', 'string', 'max:1000'],
            'post_type' => ['required', Rule::in(['text', 'poll'])],
            
            // Yêu cầu 'poll_options' NẾU post_type là 'poll'
            'poll_options' => ['required_if:post_type,poll', 'array', 'min:2'],
            'poll_options.*' => ['nullable', 'string', 'max:255'], // Kiểm tra từng lựa chọn
        ]);
        
        // 3. Sử dụng Transaction để tạo Post và các Options
        DB::transaction(function () use ($request, $topic, $team, $validated) {
            
            // 4. Tạo bài đăng (Post) trước
            $post = $topic->posts()->create([
                'content' => $validated['content'],
                'team_id' => $team->id,
                'user_id' => $request->user()->id,
                'post_type' => $validated['post_type'],
            ]);

            // 5. Nếu là Poll, tạo các lựa chọn (PollOptions)
            if ($validated['post_type'] === 'poll' && !empty($validated['poll_options'])) {
                
                // Lọc bỏ các lựa chọn rỗng (null)
                $options = array_filter($validated['poll_options']);

                // Biến mảng các chuỗi thành mảng để insert
                $pollOptionsData = array_map(function ($optionText) use ($post) {
                    return [
                        'post_id' => $post->id,
                        'text' => $optionText,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }, $options);

                // Insert hàng loạt vào DB
                DB::table('poll_options')->insert($pollOptionsData);
            }
        });

        // 6. Quay lại
        return back(303);
    }

    public function toggleComments(Request $request, Post $post)
    {
        // 1. Phân quyền: Chỉ chủ bài đăng (cả GV và HS) mới được tắt
        if ($request->user()->id !== $post->user_id) {
            abort(403);
        }

        // 2. Đảo ngược trạng thái
        $post->update([
            'are_comments_enabled' => ! $post->are_comments_enabled,
        ]);

        return back(303);
    }
}