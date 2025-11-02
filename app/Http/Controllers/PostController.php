<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use App\Models\Post;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PostController extends Controller
{
    /**
     * Lưu một bài đăng mới (Text, Poll, Material, Assignment) vào chủ đề.
     * (Hàm 'store' của bạn, giữ nguyên)
     */
    public function store(Request $request, Topic $topic)
    {
        $team = $topic->team;

        // 1. Phân quyền chung: Phải là thành viên
        if (! $request->user()->belongsToTeam($team)) {
            abort(403, 'Bạn không phải là thành viên của lớp học này.');
        }

        // 2. Phân quyền vai trò: Chỉ giáo viên (admin/editor) mới được đăng bài tập/tài liệu
        // (SỬA LẠI: Dùng $user->role theo logic của bạn)
        if (in_array($request->input('post_type'), ['assignment', 'material'])) {
            if ($request->user()->role !== 'teacher') {
                 abort(403, 'Chỉ giáo viên mới có thể đăng bài tập hoặc tài liệu.');
            }
        }
        
        // --- TẠO LOGIC VALIDATION CHO FILE (LINH HOẠT HƠN) ---
        $filesRules = ['nullable', 'array'];
        if ($request->input('post_type') === 'material') {
            // Nếu là 'Tài liệu', BẮT BUỘC phải có file và ít nhất 1 file
            $filesRules[] = 'required';
            $filesRules[] = 'min:1';
        }
        // ---------------------------------------------------

        // 3. Validate dữ liệu
        try {
            $validated = $request->validate([
                'post_type' => ['required', Rule::in(['text', 'poll', 'material', 'assignment'])],
                
                // --- Validation NỘI DUNG (content) ---
                'content' => [
                    Rule::requiredIf(in_array($request->input('post_type'), ['text', 'poll', 'assignment'])), 
                    'nullable',
                    'string',
                    'max:5000'
                ],
                
                // --- Validation FILES (SỬ DỤNG BIẾN Ở TRÊN) ---
                'files' => $filesRules,
                
                'files.*' => [
                    'file',
                    'max:20480' // 20MB max
                ], 

                // --- Validation cho Poll (Giữ nguyên) ---
                'poll_options' => ['required_if:post_type,poll', 'array', 'min:2'],
                'poll_options.*' => ['nullable', 'string', 'max:255'],
                
                // --- Validation cho Assignment (ĐÃ SỬA) ---
                'title' => [
                    'required_if:post_type,assignment',
                    'nullable', // <-- THÊM DÒNG NÀY ĐỂ SỬA LỖI
                    'string',
                    'max:255'
                ],
                'due_date' => ['nullable', 'date'],
                'max_points' => ['nullable', 'integer', 'min:0'],
            ]);
        } catch (ValidationException $e) {
            throw $e; 
        }
        
        // 4. Sử dụng Transaction để tạo Post và các dữ liệu liên quan
        $post = DB::transaction(function () use ($request, $topic, $team, $validated) {
            
            // 5. Tạo bài đăng (Post)
            $post = $topic->posts()->create([
                'content' => $validated['content'] ?? null, 
                'team_id' => $team->id,
                'user_id' => $request->user()->id,
                'post_type' => $validated['post_type'],
                'are_comments_enabled' => true,

                'title' => $validated['title'] ?? null,
                'due_date' => $validated['due_date'] ?? null,
                'max_points' => $validated['max_points'] ?? null,
            ]);

            // 6. Xử lý File Uploads (cho Material và Assignment)
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store('attachments', 'public'); 
                    
                    $post->attachments()->create([
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }
            }
            
            // 7. Xử lý Poll Options (Giữ nguyên logic của bạn)
            if ($validated['post_type'] === 'poll' && !empty($validated['poll_options'])) {
                
                $options = array_filter($validated['poll_options']);

                $pollOptionsData = array_map(function ($optionText) use ($post) {
                    return [
                        'post_id' => $post->id,
                        'text' => $optionText,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }, $options);

                DB::table('poll_options')->insert($pollOptionsData);
            }
            
            return $post; 
        });

        // 8. Quay lại
        return back(303);
    }

    /**
     * Bật/tắt bình luận (Giữ nguyên của bạn)
     */
    public function toggleComments(Request $request, Post $post)
    {
        $team = $post->team;
        $user = $request->user();

        // (SỬA LẠI: Dùng $user->role theo logic của bạn)
        if ($user->id !== $post->user_id && $user->role !== 'teacher') {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }

        $post->update([
            'are_comments_enabled' => ! $post->are_comments_enabled,
        ]);

        return back(303);
    }

    // ===== HÀM MỚI ĐỂ SỬA BÀI ĐĂNG =====
    /**
     * Cập nhật một bài đăng đã có.
     */
    public function update(Request $request, Post $post)
    {
        // 1. Kiểm tra quyền (Dùng PostPolicy.php)
        Gate::authorize('update', $post);

        // 2. Validate (Chỉ cho sửa title và content)
        $validated = $request->validate([
            'title' => [
                'required_if:post_type,assignment', // Chỉ bắt buộc nếu là bài tập
                'nullable',
                'string',
                'max:255'
            ],
            'content' => [
                'required',
                'string',
                'max:5000'
            ],
        ]);

        // 3. Cập nhật bài đăng
        $post->update($validated);

        // 4. Chuyển hướng
        return redirect()->back()->with('status', 'Đã cập nhật bài đăng thành công.');
    }


    // ===== HÀM MỚI ĐỂ XÓA BÀI ĐĂNG =====
    /**
     * Xóa một bài đăng cụ thể.
     */
    public function destroy(Request $request, Post $post)
    {
        // 1. Kiểm tra quyền (Dùng PostPolicy.php)
        Gate::authorize('delete', $post);

        // 2. (Tùy chọn): Xóa các file đính kèm trên Storage
        // foreach ($post->attachments as $attachment) {
        //     Storage::disk('public')->delete($attachment->path);
        // }
        // (Lưu ý: DB::transaction sẽ tự xóa attachments trong CSDL)

        // 3. Xóa bài đăng (và các attachments/comments liên quan)
        $post->delete();

        // 4. Chuyển hướng
        return redirect()->back()->with('status', 'Đã xóa bài đăng thành công.');
    }
}

