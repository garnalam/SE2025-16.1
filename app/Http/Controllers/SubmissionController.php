<?php

namespace App\Http\Controllers;
use App\Models\SubmissionFile;
use Illuminate\Support\Facades\Gate;
use App\Models\Post;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage; // Thêm dòng này

class SubmissionController extends Controller
{
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

        return back()->with('success', 'Nộp bài thành công!');
    }

    /**
     * Giáo viên xem danh sách bài nộp của một bài tập.
     */
    public function index(Post $post)
    {
        // TODO: Cần Policy để bảo mật, đảm bảo chỉ giáo viên của lớp mới được xem
        // $this->authorize('viewSubmissions', $post);

        // Giả sử quan hệ: Post -> Topic -> Team (Lớp học)
        // Bạn cần kiểm tra lại quan hệ này có đúng với cấu trúc của bạn không
        $team = $post->topic->team; 

        // $students = $team->users()
        //        ->wherePivot('role', 'editor') 
        //        ->get();
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
    public function grade(Request $request, Submission $submission)
    {
        // TODO: Cần Policy bảo mật
        // $this->authorize('grade', $submission);

        $maxPoints = $submission->post->max_points ?? 100; // Lấy điểm tối đa từ post

        $request->validate([
            'grade' => "required|numeric|min:0|max:{$maxPoints}",
            'feedback' => 'nullable|string|max:5000',
        ]);

        $submission->update([
            'grade' => $request->input('grade'),
            'feedback' => $request->input('feedback'),
            'graded_at' => now(),
        ]);

        return back()->with('success', 'Chấm điểm thành công!');
    }
    /**
 * Cho phép giáo viên download file bài nộp của học sinh.
 */
public function downloadFile(Request $request, SubmissionFile $submission_file)
{
    // 1. Lấy thông tin Lớp học (Team) từ file
    // Quan hệ: SubmissionFile -> Submission -> Post -> Topic -> Team
    $team = $submission_file->submission->post->topic->team;

    // 2. Phân quyền: Kiểm tra xem user có phải là Teacher/Admin của lớp này không
    // Chúng ta dùng Gate 'updateTeam' của Jetstream
    if (Gate::denies('update', $team)) {
        // Nếu không phải giáo viên của lớp, từ chối truy cập
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
}