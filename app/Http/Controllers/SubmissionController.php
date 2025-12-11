<?php

namespace App\Http\Controllers;

use App\Models\SubmissionFile;
use Illuminate\Support\Facades\Gate;
use App\Models\Post;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use App\Notifications\SubmissionGradedNotification;
use App\Notifications\LateSubmissionNotification;
use Carbon\Carbon; 
use App\Jobs\GradeSubmissionWithAI;
use App\Services\GamificationService; // <--- [MỚI] Import Service

class SubmissionController extends Controller
{
    protected $gamification; // <--- [MỚI] Khai báo biến

    // <--- [MỚI] Inject GamificationService vào Constructor
    public function __construct(GamificationService $gamification)
    {
        $this->gamification = $gamification;
    }

    /**
     * Học sinh nộp bài.
     */
    public function store(Request $request, Post $post)
    {
        $request->validate([
            'content' => 'nullable|string|max:5000',
            'files' => 'nullable|array',
            'files.*' => 'file|max:20480', // Giới hạn 20MB (20 * 1024)
        ]);

        // Sử dụng updateOrCreate để học sinh có thể nộp lại bài
        $submission = Submission::updateOrCreate(
            [
                'post_id' => $post->id,
                'user_id' => Auth::id(),
            ],
            [
                'content' => $request->input('content'),
                'submitted_at' => now(),
                'grade' => null, // Reset điểm khi nộp lại
                'feedback' => null,
                'graded_at' => null,
            ]
        );

        // Xử lý upload file
        if ($request->hasFile('files')) {
            // (Tùy chọn) Xóa file cũ nếu cho phép nộp lại
            foreach ($submission->files as $oldFile) {
                Storage::delete($oldFile->file_path); // Xóa file vật lý
                $oldFile->delete(); // Xóa record trong db
            }
            
            // Thêm file mới
            foreach ($request->file('files') as $file) {
                // Lưu file vào storage/app/submissions/{post_id}/{user_id}
                $path = $file->store("submissions/{$post->id}/" . Auth::id());
                
                $submission->files()->create([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                ]);
            }
        }

        // --- KIỂM TRA HẠN NỘP VÀ CỘNG ĐIỂM ---
        $isLate = false;

        // 1. Chỉ kiểm tra nếu bài tập có cài đặt hạn nộp (due_date)
        if ($post->due_date) {
            
            $submittedAt = Carbon::parse($submission->submitted_at);
            $dueDate = Carbon::parse($post->due_date);

            // 2. So sánh: Nếu Thời gian nộp LỚN HƠN (gt) Hạn chót -> LÀ MUỘN
            if ($submittedAt->gt($dueDate)) {
                $isLate = true; // Đánh dấu là nộp muộn

                // Lấy giáo viên (người tạo bài tập)
                $teacher = $post->user;

                // Tránh trường hợp giáo viên tự test nộp bài cho chính mình
                if ($teacher->id !== Auth::id()) {
                    // Gửi thông báo "Nộp muộn"
                    $teacher->notify(new LateSubmissionNotification($submission));
                }
            }
        }

        // [MỚI] GAMIFICATION: CỘNG ĐIỂM NẾU NỘP ĐÚNG HẠN
        if (!$isLate && Auth::user()->role === 'student') {
            // Cộng 50 XP cho việc nộp bài đúng hạn
            $this->gamification->addXp(Auth::user(), 50);
        }
        // -----------------------------------------------

        return back()->with('success', 'Nộp bài thành công!');
    }

    /**
     * Giáo viên xem danh sách bài nộp của một bài tập.
     */
    public function index(Post $post)
    {
        // Giả sử quan hệ: Post -> Topic -> Team (Lớp học)
        $team = $post->topic->team; 

        if (Gate::denies('update', $team)) {
            abort(403, 'Bạn không có quyền truy cập trang quản lý bài nộp.');
        }

        $students = $team->users()->get(); // Lấy TẤT CẢ user trong team

        // Lấy danh sách các bài đã nộp cho bài tập này
        $submissions = $post->submissions()
                            ->with('user', 'files') // Lấy kèm thông tin user và files
                            ->get()
                            ->keyBy('user_id'); // Lấy key là user_id để dễ map

        // Chuẩn bị dữ liệu cho frontend
        $submissionData = $students->map(function ($student) use ($submissions) {
                $submission = $submissions->get($student->id); // Tìm bài nộp của SV này
                
                $status = 'Not Submitted'; // Chưa nộp
                if ($submission) {
                    $status = $submission->grade !== null ? 'Graded' : 'Submitted'; // Đã chấm / Đã nộp
                }

                return [
                    'student' => [
                        'id' => $student->id,
                        'name' => $student->name,
                        'profile_photo_url' => $student->profile_photo_url,
                    ],
                    'submission' => $submission, // Sẽ là null nếu chưa nộp
                    'status' => $status,
                ];
            })->values(); // Chuyển về mảng index (thay vì key là ID)

        return Inertia::render('Submissions/Index', [
            'post' => $post->load('topic.team'), // Gửi kèm thông tin post
            'submissions' => $submissionData,
        ]);
    }

    /**
     * Giáo viên chấm điểm.
     */
/**
     * Giáo viên chấm điểm.
     */
    public function grade(Request $request, Submission $submission)
    {
        $maxPoints = $submission->post->max_points ?? 100;

        $request->validate([
            'grade' => "required|numeric|min:0|max:{$maxPoints}",
            'feedback' => 'nullable|string|max:5000',
        ]);

        $submission->update([
            'grade' => $request->input('grade'),
            'feedback' => $request->input('feedback'),
            'graded_at' => now(),
        ]);

        // =========================================================
        // [MỚI] GAMIFICATION: Kiểm tra huy hiệu sau khi chấm điểm
        // =========================================================
        
        // Chỉ kiểm tra nếu người được chấm là Học sinh
        if ($submission->user->role === 'student') {
            // Cộng XP cho việc "Được chấm điểm" (Khuyến khích) - Tùy chọn
            // $this->gamification->addXp($submission->user, 20); 

            // GỌI HÀM KIỂM TRA HUY HIỆU
            // Hệ thống sẽ tự động chạy qua PerfectScoreBadge để check xem điểm có max không
            $this->gamification->checkBadges($submission->user, $submission);
        }
        // =========================================================

        // Gửi thông báo đến chính học sinh sở hữu bài nộp này
        $submission->user->notify(new SubmissionGradedNotification($submission));
        
        return back()->with('success', 'Chấm điểm thành công!');
    }

    /**
     * Cho phép giáo viên download file bài nộp của học sinh.
     */
    public function downloadFile(Request $request, SubmissionFile $submission_file)
    {
        // 1. Lấy thông tin Lớp học (Team) từ file
        $team = $submission_file->submission->post->topic->team;

        // 2. Phân quyền: Kiểm tra xem user có phải là Teacher/Admin của lớp này không
        if (Gate::denies('update', $team)) {
            abort(403, 'Bạn không có quyền truy cập file này.');
        }

        // 3. Kiểm tra file có tồn tại không
        if (!Storage::exists($submission_file->file_path)) {
            abort(404, 'File không tồn tại.');
        }

        // 4. Trả về file cho user download (với tên file gốc)
        return Storage::download(
            $submission_file->file_path, 
            $submission_file->original_name
        );
    }

    /**
     * Kích hoạt AI chấm bài.
     */
    public function requestAiGrading(Request $request, Submission $submission)
    {
        // 1. Kiểm tra quyền (Giáo viên của lớp mới được chấm)
        $team = $submission->post->topic->team;
        if (Gate::denies('update', $team)) {
            abort(403);
        }

        // 2. Kiểm tra nội dung
        if (!$submission->content && $submission->files->count() === 0) {
            return back()->with('error', 'Bài làm trống, AI không thể phân tích.');
        }

        // 3. Đẩy Job vào hàng đợi (Queue)
        GradeSubmissionWithAI::dispatch($submission);

        return back()->with('success', 'Đã gửi yêu cầu cho AI. Vui lòng đợi khoảng 10-20 giây rồi tải lại trang.');
    }
}