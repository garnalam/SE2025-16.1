<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\Post;
use App\Models\Submission;
use Illuminate\Http\Request;
// [RESOLVED] Giữ lại imports từ branch dev
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    public function getClassAnalytics($classroomId)
    {
        // 1. Xác thực
        if (auth()->id() !== $team->user_id) {
            abort(403, 'Bạn không có quyền xem lớp này.');
        }
    
        // 2. Lấy dữ liệu cơ bản
        // Eager load submissions và quizAttempts để tính điểm giống Gradebook
        $studentUsers = $team->users()
            ->where('users.role', 'student')
            ->with(['submissions', 'quizAttempts'])
            ->get();
            
        $studentCount = $studentUsers->count();
        
        if ($studentCount === 0) {
            return response()->json(['message' => 'Lớp học chưa có học sinh.'], 200); // Trả về 200 để FE không lỗi
        }

        // 3. Lấy danh sách Bài tập & Quiz (Logic giống Gradebook)
        $quizzes = Post::where('team_id', $team->id)
            ->where('post_type', 'quiz')
            ->get(['id', 'title', 'max_points']);

        $assignments = Post::where('team_id', $team->id)
            ->where('post_type', 'assignment')
            ->get(['id', 'title', 'max_points']);

        // 4. Tính điểm tổng kết cho từng học sinh (Logic Gradebook)
        $studentGrades = $studentUsers->map(function ($student) use ($quizzes, $assignments) {
            // --- TÍNH ĐIỂM QUIZ ---
            $totalQuizScore = 0;
            $totalQuizMax = 0;
            foreach ($quizzes as $quiz) {
                $attempt = $student->quizAttempts->where('post_id', $quiz->id)->sortByDesc('score')->first();
                if ($attempt) {
                    $totalQuizScore += $attempt->score;
                    $totalQuizMax += ($quiz->max_points ?? 10);
                }
            }
            $quizAvg = ($totalQuizMax > 0) ? ($totalQuizScore / $totalQuizMax) * 10 : 0;

            // --- TÍNH ĐIỂM ASSIGNMENT ---
            $totalAssignScore = 0;
            $totalAssignMax = 0;
            foreach ($assignments as $assign) {
                $sub = $student->submissions->where('post_id', $assign->id)->first();
                if ($sub && $sub->grade !== null) {
                    $totalAssignScore += $sub->grade;
                    $totalAssignMax += ($assign->max_points ?? 100);
                }
            }
            $assignAvg = ($totalAssignMax > 0) ? ($totalAssignScore / $totalAssignMax) * 10 : 0;

            // --- TỔNG KẾT (40% Quiz - 60% Assign) ---
            $overallAvg = round(($quizAvg * 0.4) + ($assignAvg * 0.6), 2);

            return [
                'id' => $student->id,
                'name' => $student->name,
                'overall_avg' => $overallAvg
            ];
        });

        // 5. Tạo dữ liệu cho các biểu đồ
        $chartGradeDistribution = $this->calculateGradeDistribution($studentGrades);    
        $chartCompletionRate = $this->calculateCompletionRateChart($assignments, $quizzes, $studentUsers);
        $chartStudentPerformance = $this->calculateStudentPerformanceChart($studentGrades);
    
        return response()->json([
            'chartGradeDistribution'  => $chartGradeDistribution,
            'chartCompletionRate'     => $chartCompletionRate,
            'chartStudentPerformance' => $chartStudentPerformance,
        ]);
    }

    /**
     * BIỂU ĐỒ 1: PHÂN PHỐI ĐIỂM (Thang 10: 0-2, 2-4, 4-6, 6-8, 8-10)
     */
    private function calculateGradeDistribution($studentGrades)
    {
        $counts = [
            '0-2' => 0, '2-4' => 0, '4-6' => 0, '6-8' => 0, '8-10' => 0
        ];

        foreach ($studentGrades as $student) {
            $score = $student['overall_avg'];
            
            if ($score < 2) $counts['0-2']++;
            elseif ($score < 4) $counts['2-4']++;
            elseif ($score < 6) $counts['4-6']++;
            elseif ($score < 8) $counts['6-8']++;
            else $counts['8-10']++;
        }

        $studentCount = $studentGrades->count();
        $data = [];
        $labels = array_keys($counts);

        foreach ($counts as $count) {
            // Đã kiểm tra $studentCount > 0 ở đầu hàm, nhưng check lại cho chắc
            $percentage = ($studentCount > 0) ? round(($count / $studentCount) * 100, 2) : 0;
            $data[] = $percentage;
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => 'Tỉ lệ học sinh (%)',
                'data' => $data,
                'backgroundColor' => ['#ef4444', '#f97316', '#eab308', '#3b82f6', '#10b981'],
            ]],
        ];
    }

    /**
     * BIỂU ĐỒ 2: TỶ LỆ HOÀN THÀNH (Gộp cả Quiz và Assignment)
     */
    private function calculateCompletionRateChart($assignments, $quizzes, $studentUsers)
    {
        $labels = [];
        $completionRateData = [];
        $studentCount = $studentUsers->count();

        // Gộp chung 2 collection để hiển thị trên cùng 1 biểu đồ
        $allPosts = $assignments->concat($quizzes);

        foreach ($allPosts as $post) {
            $labels[] = \Illuminate\Support\Str::limit($post->title, 15);

            // Đếm số người nộp
            // Kiểm tra xem post là Quiz hay Assignment dựa vào quan hệ model (hoặc check logic đơn giản)
            // Ở đây ta check trực tiếp trên collection submissions/quizAttempts của students đã eager load
            $submittedCount = 0;
            
            foreach($studentUsers as $student) {
                $hasSubmitted = false;
                // Check assignment submission
                if ($student->submissions->where('post_id', $post->id)->count() > 0) {
                    $hasSubmitted = true;
                }
                // Check quiz attempt
                elseif ($student->quizAttempts->where('post_id', $post->id)->count() > 0) {
                    $hasSubmitted = true;
                }

                if ($hasSubmitted) $submittedCount++;
            }

            $rate = ($studentCount > 0) ? ($submittedCount / $studentCount) * 100 : 0;
            $completionRateData[] = round($rate, 2);
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => 'Tỷ lệ hoàn thành (%)',
                'data' => $completionRateData,
                'backgroundColor' => 'rgba(6, 182, 212, 0.6)',
            ]],
        ];
    }

    /**
     * BIỂU ĐỒ 3: HIỆU SUẤT HỌC SINH (Dùng điểm tổng kết Gradebook)
     */
    private function calculateStudentPerformanceChart($studentGrades)
    {
        if ($studentGrades->isEmpty()) {
             return ['labels' => [], 'datasets' => []];
        }

        $labels = []; 
        $studentScores = []; 

        foreach ($studentGrades as $student) {
            $labels[] = $student['name'];
            $studentScores[] = $student['overall_avg'];
        }

        // Tính trung bình lớp
        $classAverage = (count($studentScores) > 0) 
            ? round(array_sum($studentScores) / count($studentScores), 2)
            : 0;

        $classAverageLineData = array_fill(0, count($labels), $classAverage);

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'type' => 'bar',
                    'label' => 'Điểm tổng kết (Thang 10)',
                    'data' => $studentScores,
                    'backgroundColor' => 'rgba(6, 182, 212, 0.7)',
                    'order' => 2 
                ],
                [
                    'type' => 'line',
                    'label' => 'Trung bình lớp',
                    'data' => $classAverageLineData,
                    'borderColor' => 'rgba(244, 63, 94, 1)',
                    'borderWidth' => 2,
                    'fill' => false,
                    'tension' => 0.1,
                    'order' => 1 
                ]
            ]
        ];
    }

    // [RESOLVED] Giữ lại hàm gradebook từ branch dev
    public function gradebook(Request $request, Team $team)
    {
        $user = Auth::user();
        
        // 1. Check quyền truy cập Team
        if (!$user->belongsToTeam($team)) {
            abort(403);
        }

        // 2. Xác định vai trò trong Team
        $membership = $team->users()->where('user_id', $user->id)->first();
        $role = ($team->user_id === $user->id) ? 'owner' : ($membership ? $membership->membership->role : null);

        // 3. Lấy danh sách Bài tập và Quiz
        $quizzes = Post::where('team_id', $team->id)
            ->where('post_type', 'quiz')
            ->orderBy('created_at')
            ->get(['id', 'title', 'max_points']);

        $assignments = Post::where('team_id', $team->id)
            ->where('post_type', 'assignment')
            ->orderBy('due_date')
            ->get(['id', 'title', 'max_points']);

        // 4. Lấy danh sách học sinh cần xem điểm
        $query = $team->users()->wherePivot('role', 'student');

        if ($role === 'student') {
            $query->where('users.id', $user->id);
        }

        $students = $query->with(['submissions', 'quizAttempts'])->get()->map(function ($student) use ($quizzes, $assignments) {
            
            // Logic giống hàm show ở trên, nhưng chi tiết hơn để hiển thị bảng
            $quizGrades = [];
            $totalQuizScore = 0;
            $totalQuizMax = 0;

            foreach ($quizzes as $quiz) {
                $attempt = $student->quizAttempts->where('post_id', $quiz->id)->sortByDesc('score')->first();
                $score = $attempt ? $attempt->score : null;
                $max = $quiz->max_points ?? 10;

                $quizGrades[$quiz->id] = $score;
                
                if ($score !== null) {
                    $totalQuizScore += $score;
                    $totalQuizMax += $max;
                }
            }
            $quizAvg = ($totalQuizMax > 0) ? round(($totalQuizScore / $totalQuizMax) * 10, 2) : 0;

            $assignGrades = [];
            $totalAssignScore = 0;
            $totalAssignMax = 0;

            foreach ($assignments as $assign) {
                $sub = $student->submissions->where('post_id', $assign->id)->first();
                $grade = $sub ? $sub->grade : null;
                $max = $assign->max_points ?? 100;

                $assignGrades[$assign->id] = $grade;

                if ($grade !== null) {
                    $totalAssignScore += $grade;
                    $totalAssignMax += $max;
                }
            }
            $assignAvg = ($totalAssignMax > 0) ? round(($totalAssignScore / $totalAssignMax) * 10, 2) : 0;

            $overallAvg = round(($quizAvg * 0.4) + ($assignAvg * 0.6), 2);

            return [
                'id' => $student->id,
                'name' => $student->name,
                'avatar' => $student->profile_photo_url,
                'quiz_grades' => $quizGrades,
                'quiz_avg' => $quizAvg,
                'assign_grades' => $assignGrades,
                'assign_avg' => $assignAvg,
                'overall_avg' => $overallAvg,
            ];
        });

        return Inertia::render('Gradebook/Index', [
            'team' => $team,
            'headers' => [
                'quizzes' => $quizzes,
                'assignments' => $assignments,
            ],
            'students' => $students,
            'isTeacher' => ($role !== 'student'),
        ]);
    }
}
