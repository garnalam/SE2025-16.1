<?php

namespace App\Services;

use App\Models\Team;
use App\Models\User;
use App\Models\Post;
// 👇 Giả định bạn có các model này cho điểm danh
use App\Models\AttendanceSession; 
use App\Models\AttendanceRecord;

class GradebookService
{
    public function getClassGradebook($teamId)
    {
        $team = Team::findOrFail($teamId);
        $weights = $team->grade_weights ?? ['attendance' => 10, 'regular' => 50, 'midterm' => 20, 'final' => 20];
        
        $students = $team->users()->where('users.role', 'student')->get();
        $allPosts = Post::where('team_id', $teamId)->whereIn('post_type', ['quiz', 'assignment'])->get();

        // 👇 Lấy tổng số buổi điểm danh của lớp
        // (Nếu chưa có model AttendanceSession, bạn có thể tạm để = 10 hoặc thay bằng logic của bạn)
        $totalSessions = AttendanceSession::where('team_id', $teamId)->count();

        $gradebook = $students->map(function ($student) use ($allPosts, $weights, $totalSessions, $teamId) {
            return $this->calculateStudentGrades($student, $allPosts, $weights, $totalSessions, $teamId);
        });

        return [
            'weights' => $weights,
            'posts' => $allPosts->map(fn($p) => [
                'id' => $p->id,
                'title' => $p->title ?? 'Bài #'.$p->id,
                'type' => $p->post_type,
                'grading_type' => $p->grading_type, 
                'max_points' => $p->max_points ?? 10
            ]),
            'students_data' => $gradebook
        ];
    }

    public function getStudentGradebook($teamId, $userId)
    {
        $team = Team::findOrFail($teamId);
        $student = User::findOrFail($userId);
        $weights = $team->grade_weights ?? ['attendance' => 10, 'regular' => 50, 'midterm' => 20, 'final' => 20];
        
        $allPosts = Post::where('team_id', $teamId)->whereIn('post_type', ['quiz', 'assignment'])->get();
        
        // Lấy tổng buổi
        $totalSessions = AttendanceSession::where('team_id', $teamId)->count();

        $calculated = $this->calculateStudentGrades($student, $allPosts, $weights, $totalSessions, $teamId);

        $postsWithDetails = $allPosts->map(function($post) use ($calculated) {
            return [
                'id' => $post->id,
                'title' => $post->title ?? 'Bài #'.$post->id,
                'type' => $post->post_type, 
                'grading_type' => $post->grading_type,
                'score' => $calculated['details'][$post->id] ?? 0,
                'max_points' => $post->max_points ?? 10,
            ];
        });

        return array_merge($calculated, ['posts_list' => $postsWithDetails]);
    }

    private function calculateStudentGrades($student, $allPosts, $weights, $totalSessions, $teamId)
    {
        $midtermPost = $allPosts->firstWhere('grading_type', 'midterm');
        $finalPost = $allPosts->firstWhere('grading_type', 'final');
        $regularPosts = $allPosts->where('grading_type', 'regular');

        $details = [];

        // Helper lấy điểm
        $getScore = function($post) use ($student) {
            if (!$post) return 0;
            if ($post->post_type === 'quiz') {
                $attempt = $student->quizAttempts()->where('post_id', $post->id)->orderByDesc('score')->first();
                return $attempt ? $attempt->score : 0;
            } else {
                $sub = $student->submissions()->where('post_id', $post->id)->first();
                return $sub ? ($sub->grade ?? 0) : 0;
            }
        };

        // 1. Tính Regular
        $totalEarned = 0;
        $totalMax = 0;
        foreach ($regularPosts as $post) {
            $s = $getScore($post);
            $details[$post->id] = $s;
            $totalEarned += $s;
            $totalMax += ($post->max_points ?? 10);
        }
        $regularAvg = ($totalMax > 0) ? ($totalEarned / $totalMax) * 10 : 0;

        // 2. Tính Midterm & Final
        $midtermScore = $getScore($midtermPost);
        if($midtermPost) $details[$midtermPost->id] = $midtermScore;

        $finalScore = $getScore($finalPost);
        if($finalPost) $details[$finalPost->id] = $finalScore;

        // --- 3. TÍNH CHUYÊN CẦN (MỚI) ---
        // Đếm số buổi có mặt (status = 'present' hoặc 'late')
        $attendedCount = AttendanceRecord::where('user_id', $student->id)
            ->whereHas('session', fn($q) => $q->where('team_id', $teamId))
            ->whereIn('status', ['present', 'late']) // Tùy logic của bạn
            ->count();
        
        // Tính % và điểm chuyên cần (thang 10)
        $attendancePercent = $totalSessions > 0 ? ($attendedCount / $totalSessions) : 1; // Mặc định 100% nếu chưa có buổi nào
        $attendanceScore = $attendancePercent * 10;

        // 4. Tổng kết
        $totalScore = (
            ($regularAvg * ($weights['regular'] ?? 0)) +
            ($midtermScore * ($weights['midterm'] ?? 0)) +
            ($finalScore * ($weights['final'] ?? 0)) +
            ($attendanceScore * ($weights['attendance'] ?? 0))
        ) / 100;

        return [
            'student' => $student,
            'details' => $details,
            'regular_avg' => round($regularAvg, 2),
            'midterm_score' => round($midtermScore, 2),
            'final_score' => round($finalScore, 2),
            // Data chuyên cần
            'attendance_stats' => [
                'attended' => $attendedCount,
                'total' => $totalSessions,
                'percent' => round($attendancePercent * 100, 0),
                'score' => round($attendanceScore, 2)
            ],
            'overall_avg' => round($totalScore, 2),
            'current_weights' => $weights,
        ];
    }
    
    // Hàm updateSettings giữ nguyên
    public function updateSettings($teamId, $data)
    {
        $team = Team::findOrFail($teamId);
        $team->grade_weights = $data['weights'];
        $team->save();

        Post::where('team_id', $teamId)->update(['grading_type' => 'regular']);
        
        if (!empty($data['midterm_id'])) {
            Post::where('id', $data['midterm_id'])->update(['grading_type' => 'midterm']);
        }
        if (!empty($data['final_id'])) {
            Post::where('id', $data['final_id'])->update(['grading_type' => 'final']);
        }
    }
}