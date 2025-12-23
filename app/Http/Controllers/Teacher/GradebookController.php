<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\Post;
use App\Models\User;
use App\Models\Submission;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class GradebookController extends Controller
{
    public function index(Team $team)
    {
        $user = auth()->user();
        
        // 1. Phân quyền
        $isTeacher = $user->id === $team->user_id || $user->hasTeamRole($team, 'teacher');
        $isStudent = $user->hasTeamRole($team, 'student');

        if (!$isTeacher && !$isStudent) {
            abort(403);
        }

        // 2. Lấy Quiz & Assignment
        $quizzes = Post::where('team_id', $team->id)
            ->where('post_type', 'quiz') 
            ->orderBy('created_at', 'asc')
            ->get(['id', 'title', 'max_points']);

        $assignments = Post::where('team_id', $team->id)
            ->where('post_type', 'assignment')
            ->orderBy('due_date', 'asc')
            ->get(['id', 'title', 'max_points', 'due_date']);

        // ---------------------------------------------------------
        // 3. LẤY HỌC SINH (HYBRID APPROACH)
        // ---------------------------------------------------------
        
        // B1: Query Builder để lấy danh sách ID (Bỏ qua Global Scope)
        $query = DB::table('team_user')
            ->where('team_id', $team->id)
            ->where('role', 'student') // Lọc cứng role student
            ->select('user_id'); 

        // [QUAN TRỌNG] FIX LỖI Ở ĐÂY:
        // Chỉ lọc theo ID cá nhân nếu là Học sinh VÀ KHÔNG PHẢI LÀ GIÁO VIÊN.
        // Điều này đảm bảo giáo viên luôn nhìn thấy tất cả, ngay cả khi tài khoản bị lỗi role.
        if ($isStudent && !$isTeacher) {
            $query->where('user_id', $user->id);
        }

        // Paginate
        $paginator = $query->paginate(50);

        // B2: Lấy danh sách ID
        $studentIds = $paginator->pluck('user_id')->toArray();

        // B3: Load User Model thật
        $studentModels = User::whereIn('id', $studentIds)
            ->orderBy('name')
            ->get()
            ->keyBy('id');

        // ---------------------------------------------------------
        // 4. LẤY DỮ LIỆU ĐIỂM SỐ
        // ---------------------------------------------------------
        $allSubmissions = Submission::whereIn('post_id', $assignments->pluck('id'))
            ->whereIn('user_id', $studentIds)
            ->get();

        $allAttempts = QuizAttempt::whereIn('post_id', $quizzes->pluck('id'))
            ->whereIn('user_id', $studentIds)
            ->get();

        // ---------------------------------------------------------
        // 5. TÍNH TOÁN
        // ---------------------------------------------------------
        
        $gradebookData = $studentModels->map(function ($student) use ($assignments, $quizzes, $allSubmissions, $allAttempts, $team) {
            
            // Logic Quiz
            $quizGrades = [];
            $quizTotalEarned = 0;
            $quizTotalMax = 0;
            foreach ($quizzes as $quiz) {
                $maxScore = $allAttempts->where('user_id', $student->id)
                                        ->where('post_id', $quiz->id)
                                        ->max('score');
                $quizGrades[$quiz->id] = $maxScore;
                if ($maxScore !== null) {
                    $quizTotalEarned += $maxScore;
                    $quizTotalMax += $quiz->max_points;
                }
            }
            $quizAvg = ($quizTotalMax > 0) ? ($quizTotalEarned / $quizTotalMax) * 10 : 0;

            // Logic Assignment
            $assignGrades = [];
            $assignTotalEarned = 0;
            $assignTotalMax = 0;
            foreach ($assignments as $assign) {
                $sub = $allSubmissions->where('user_id', $student->id)->where('post_id', $assign->id)->first();
                $info = $this->calculateAssignmentGrade($sub, $assign, $team);
                $assignGrades[$assign->id] = $info;
                if ($info['status'] !== 'pending' && $info['status'] !== 'submitted') {
                    $assignTotalEarned += $info['final_score'];
                    $assignTotalMax += $assign->max_points;
                }
            }
            $assignAvg = ($assignTotalMax > 0) ? ($assignTotalEarned / $assignTotalMax) * 10 : 0;

            return [
                'id' => $student->id,
                'name' => $student->name,
                'avatar' => $student->profile_photo_url, 
                'quizzes' => $quizGrades,
                'quiz_avg' => round($quizAvg, 2),
                'assignments' => $assignGrades,
                'assign_avg' => round($assignAvg, 2),
            ];
        });

        $paginator->setCollection($gradebookData->values());

        return Inertia::render('Teams/Gradebook', [
            'team' => $team,
            'assignments' => $assignments,
            'quizzes' => $quizzes,
            'gradebook' => $paginator,
            'settings' => [
                'late_policy_type' => $team->late_policy_type ?? 'none',
                'late_penalty_percent' => $team->late_penalty_percent ?? 0,
            ],
            'canManage' => $isTeacher, 
        ]);
    }

    public function updateSettings(Request $request, Team $team)
    {
        if (auth()->id() !== $team->user_id && !auth()->user()->hasTeamRole($team, 'teacher')) {
            abort(403);
        }

        $validated = $request->validate([
            'late_policy_type' => 'required|in:none,fixed,daily',
            'late_penalty_percent' => 'required|integer|min:0|max:100',
        ]);

        $team->update([
            'late_policy_type' => $validated['late_policy_type'],
            'late_penalty_percent' => $validated['late_penalty_percent'],
        ]);

        return back()->with('success', 'Đã cập nhật cấu hình tính điểm.');
    }

    private function calculateAssignmentGrade($submission, $assignment, $team)
    {
        if (!$submission) {
            if ($assignment->due_date && Carbon::now()->gt($assignment->due_date)) {
                return ['status' => 'missing', 'raw' => 0, 'final_score' => 0, 'penalty' => 0, 'late_days' => 0];
            }
            return ['status' => 'pending', 'final_score' => null];
        }

        if ($submission->grade === null) return ['status' => 'submitted', 'final_score' => null];

        $submittedAt = $submission->submitted_at ? Carbon::parse($submission->submitted_at) : Carbon::parse($submission->created_at);
        $dueDate = $assignment->due_date ? Carbon::parse($assignment->due_date) : null;
        $isLate = $dueDate && $submittedAt->gt($dueDate->copy()->addMinute());

        $penalty = 0;
        $lateDays = 0;

        if ($isLate && $team->late_policy_type !== 'none') {
            $diffRaw = abs($submittedAt->floatDiffInDays($dueDate)); 
            $lateDays = ceil($diffRaw); 
            $maxPoints = (float) $assignment->max_points;
            $percentConfig = abs((int) $team->late_penalty_percent);

            if ($team->late_policy_type === 'fixed') {
                $penalty = $maxPoints * ($percentConfig / 100);
            } elseif ($team->late_policy_type === 'daily') {
                $totalPercent = min($percentConfig * $lateDays, 100);
                $penalty = $maxPoints * ($totalPercent / 100);
            }
        }
        $finalScore = max(0, $submission->grade - $penalty);

        return [
            'status' => $isLate ? 'late' : 'graded',
            'raw' => $submission->grade,
            'final_score' => round($finalScore, 2),
            'penalty' => round($penalty, 2),
            'late_days' => $lateDays
        ];
    }
}