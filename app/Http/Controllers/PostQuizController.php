<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Question;
use Illuminate\Http\Request;
use Inertia\Inertia; // <-- Quan trọng: dùng Inertia
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification; // <--- THÊM DÒNG NÀY
use App\Notifications\NewPostNotification;   // <--- THÊM DÒNG NÀY
use App\Models\QuizTemplate; // Thêm
use App\Models\User;        // Thêm
use Illuminate\Support\Facades\DB; // Thêm

class PostQuizController extends Controller
{
    /**
     * Hiển thị trang quản lý câu hỏi cho một bài quiz (Post).
     * Trang này sẽ có 2 cột: câu hỏi đã thêm & câu hỏi có sẵn trong ngân hàng.
     */
    public function manage(Request $request, Post $post)
    {
        Gate::authorize('update', $post); 

        // 1. Lấy câu hỏi ĐÃ CÓ trong quiz (cho Tab 1)
        $quizQuestions = $post->questions()->with('options')->get();
        $quizQuestionIds = $quizQuestions->pluck('id');

        // 2. Lấy câu hỏi CÓ THỂ THÊM (cho Tab 1)
        $availableQuery = auth()->user()->questions()
            ->whereNotIn('id', $quizQuestionIds)
            ->with('options', 'subject', 'tags'); // Tải kèm subject/tags để hiển thị

        // 2.1. Lọc theo Tên (Nội dung)
        if ($request->filled('filter_search')) {
            $availableQuery->where('question_text', 'like', '%' . $request->input('filter_search') . '%');
        }

        // 2.2. Lọc theo Môn học
        if ($request->filled('filter_subject')) {
            $availableQuery->where('subject_id', $request->input('filter_subject'));
        }

        // 2.3. Lọc theo Thẻ (Tags) - Tìm câu hỏi có CHỨA thẻ này
        if ($request->filled('filter_tags')) {
            $tagIds = $request->input('filter_tags'); // Giả sử đây là mảng các ID
            $availableQuery->whereHas('tags', function ($q) use ($tagIds) {
                $q->whereIn('tags.id', $tagIds);
            });
        }

        $availableQuestions = $availableQuery->latest()->paginate(10)->withQueryString();

        // 3. Lấy dữ liệu cho Tab 2 (Tạo ngẫu nhiên)
        $user = auth()->user();
        $templates = $user->quizTemplates()->orderBy('name')->get(); // Lấy Mẫu Cấu hình
        $subjects = $user->subjects()->orderBy('name')->get();
        $tags = $user->tags()->orderBy('name')->get();

        // 4. Lấy danh sách học sinh trong lớp (cho tính năng Giao bài)
        $students = $post->team->users() // <-- Sửa 1: Thêm () để lấy Query Builder
                ->where('users.role', 'student') // Sửa 2: Chỉ định rõ bảng 'users.role'
                ->orderBy('users.name')            // Sửa 3: Chỉ định rõ bảng 'users.name'
                ->get(['users.id', 'users.name']); // Sửa 4: Chỉ định rõ bảng 'users.id', 'users.name'

        // 5. Lấy danh sách học sinh đã được giao bài này
        $assignedUserIds = $post->assignedUsers()->pluck('users.id');

        return Inertia::render('Posts/Quiz/Manage', [
            'post' => $post->load('topic:id,team_id'), // Tải post

            // Dữ liệu cho Tab 1 (Thủ công)
            'quizQuestions' => $quizQuestions,
            'availableQuestions' => $availableQuestions,

            // Dữ liệu cho Tab 2 (Ngẫu nhiên)
            'subjects' => $subjects,
            'tags' => $tags,
            'templates' => $templates,
            'students' => $students,

            // Cài đặt hiện tại của Quiz
            'currentSettings' => [
                'quiz_mode' => $post->quiz_mode,
                'shuffle' => $post->shuffle_questions,
                'points' => $post->points_per_question,
                'is_proctored' => !!$post->is_proctored, // <--- TRUYỀN XUỐNG VUE
                'assignedUserIds' => $assignedUserIds,
                'assign_mode' => $assignedUserIds->count() == $students->count() || $assignedUserIds->count() == 0 ? 'all' : 'specific',
            ],
            'filters' => $request->only(['filter_search', 'filter_subject', 'filter_tags']),
            
        ]);
    }

    /**
     * Gắn một câu hỏi (từ ngân hàng) vào bài quiz.
     */
    public function attach(Request $request, Post $post)
    {
        // 1. Kiểm tra quyền
        Gate::authorize('update', $post);

        // 2. Validate
        $request->validate([
            'question_id' => 'required|exists:questions,id'
        ]);

        $question = Question::find($request->question_id);

        // 3. Kiểm tra bảo mật BẮT BUỘC: 
        // Đảm bảo giáo viên này sở hữu câu hỏi họ đang cố thêm
        if ($question->user_id !== auth()->id()) {
            abort(403, 'Bạn không sở hữu câu hỏi này.');
        }

        // 4. Gắn câu hỏi vào (dùng syncWithoutDetaching để tránh trùng lặp)
        $post->questions()->syncWithoutDetaching($question->id);

        return redirect()->back()->with('success', 'Đã thêm câu hỏi.');
    }

    /**
     * Gỡ một câu hỏi ra khỏi bài quiz.
     */
    public function detach(Request $request, Post $post)
    {
        // 1. Kiểm tra quyền
        Gate::authorize('update', $post);

        // 2. Validate
        $request->validate([
            'question_id' => 'required|exists:questions,id'
        ]);

        // 3. Gỡ câu hỏi
        $post->questions()->detach($request->question_id);

        return redirect()->back()->with('success', 'Đã gỡ câu hỏi.');
    }
    public function generate(Request $request, Post $post)
    {
        Gate::authorize('update', $post);

        $data = $request->validate([
            'settings' => 'required|array',
            'settings.subject_id' => 'required|integer|exists:subjects,id',
            'settings.tags' => 'nullable|array',
            'settings.tags.*' => 'integer|exists:tags,id',
            'settings.count' => 'required|integer|min:1',
            'settings.shuffle' => 'required|boolean',
            'settings.points' => 'required|numeric|min:0.1',
            'settings.is_proctored' => 'required|boolean', // <--- THÊM DÒNG NÀY
            'assignment' => 'required|array',
            'assignment.assign_mode' => 'required|in:all,specific',
            'assignment.assigned_users' => 'nullable|array',
            'assignment.assigned_users.*' => 'integer|exists:users,id',
        ]);

        $settings = $data['settings'];
        $assignment = $data['assignment'];

        // 1. Tìm các câu hỏi phù hợp
        $query = Question::query()
            ->where('user_id', auth()->id()) // Chỉ lấy câu hỏi của giáo viên
            ->where('subject_id', $settings['subject_id']);

        if (!empty($settings['tags'])) {
            $query->whereHas('tags', function ($q) use ($settings) {
                $q->whereIn('tags.id', $settings['tags']);
            }, '=', count($settings['tags']));
        }

        // 2. Lấy ngẫu nhiên
        $questionIds = $query->inRandomOrder()
                            ->limit($settings['count'])
                            ->pluck('id');

        // 3. Kiểm tra nếu không đủ câu hỏi
        if ($questionIds->count() < $settings['count']) {
            return redirect()->back()->withErrors([
                'settings.count' => 'Không tìm thấy đủ ' . $settings['count'] . ' câu hỏi. Chỉ tìm thấy ' . $questionIds->count() . ' câu.'
            ]);
        }

        // 4. Bắt đầu lưu vào DB
        $assignedIds = [];
        DB::transaction(function () use ($post, $questionIds, $settings, $assignment, $assignedIds) {

            // 4.1. Cập nhật bài Post với cài đặt mới
            $post->update([
                'is_proctored' => $settings['is_proctored'], // <--- LƯU VÀO DB
                'quiz_mode' => 'random',
                'shuffle_questions' => $settings['shuffle'],
                'points_per_question' => $settings['points'],
                'max_points' => $settings['count'] * $settings['points'],
                'random_quiz_settings' => [ // Lưu lại cài đặt đã dùng
                    'subject_id' => $settings['subject_id'],
                    'tags' => $settings['tags'],
                    'count' => $settings['count'],
                ],
            ]);

            // 4.2. Gắn (sync) các câu hỏi ngẫu nhiên vào bài quiz
            $post->questions()->sync($questionIds);

            // 4.3. Xử lý Giao bài (Assign)
            if ($assignment['assign_mode'] === 'all') {
                // Giao cho tất cả học sinh trong lớp
                $allStudentIds = $post->team->users()
                    ->where('users.role', 'student') // <-- SỬA LỖI Ở ĐÂY
                    ->pluck('users.id');
                $post->assignedUsers()->sync($allStudentIds);
                $assignedIds = $allStudentIds; // Lưu lại để gửi thông báo
            } else {
                // Chỉ giao cho các học sinh cụ thể
                $post->assignedUsers()->sync($assignment['assigned_users']);
                $assignedIds = $assignment['assigned_users']; // Lưu lại
            }
        });
        // Lấy danh sách User Object từ mảng ID
        if (!empty($assignedIds)) {
             $studentsToNotify = User::whereIn('id', $assignedIds)->get();
             Notification::send($studentsToNotify, new NewPostNotification($post));
        }
        return redirect()->back()->with('success', 'Đã tạo đề ngẫu nhiên thành công với ' . $questionIds->count() . ' câu hỏi.');
    }
    public function saveManualSettings(Request $request, Post $post)
    {
        Gate::authorize('update', $post);

        $data = $request->validate([
            'settings.shuffle' => 'required|boolean',
            'settings.is_proctored' => 'required|boolean', // <--- THÊM DÒNG NÀY
            'assignment.assign_mode' => 'required|in:all,specific',
            'assignment.assigned_users' => 'nullable|array',
            'assignment.assigned_users.*' => 'integer|exists:users,id',
        ]);

        $settings = $data['settings'];
        $assignment = $data['assignment'];
        $assignedIds = []; // Biến tạm
        DB::transaction(function () use ($post, $settings, $assignment,$assignedIds) {

            // 1. Cập nhật bài Post
            $post->update([
                'quiz_mode' => 'manual', // Đặt chế độ là Thủ công
                'shuffle_questions' => $settings['shuffle'],
                'random_quiz_settings' => null, // Xóa cài đặt ngẫu nhiên (nếu có)
                // Chúng ta không cập nhật 'points' vì chế độ thủ công có thể có nhiều loại câu hỏi
                'is_proctored' => $settings['is_proctored'], // <--- LƯU VÀO DB
            ]);

            // 2. Xử lý Giao bài (Assign)
            if ($assignment['assign_mode'] === 'all') {
                $allStudentIds = $post->team->users()
                                    ->where('users.role', 'student') // Sửa lỗi ambiguous
                                    ->pluck('users.id');
                $post->assignedUsers()->sync($allStudentIds);
                $assignedIds = $allStudentIds;
            } else {
                $post->assignedUsers()->sync($assignment['assigned_users']);
                $assignedIds = $assignment['assigned_users'];
            }
        });
        if (!empty($assignedIds)) {
             $studentsToNotify = User::whereIn('id', $assignedIds)->get();
             Notification::send($studentsToNotify, new NewPostNotification($post));
        }
        return redirect()->back()->with('success', 'Đã lưu cài đặt cho bài quiz thủ công.');
    }
}