<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\Post;
use App\Models\Submission;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function getClassAnalytics($classroomId)
    {
        $team = Team::findOrFail($classroomId);

        if (auth()->id() !== $team->user_id) {
            abort(403, 'Bạn không có quyền xem lớp này.');
        }

        // Lấy danh sách học sinh
        $studentUsers = $team->users()->where('team_user.role', 'student')->get(['users.id', 'users.name']);
        $studentIds = $studentUsers->pluck('id');
        $studentCount = $studentIds->count();
        
        // --- SỬA LOGIC TẠI ĐÂY ---
        // KHÔNG trả về 404 nữa. Nếu không có học sinh, code vẫn chạy tiếp xuống dưới.
        // Các hàm tính toán bên dưới đã được viết để xử lý trường hợp chia cho 0 (division by zero).
        
        $assignments = Post::where('team_id', $team->id)
                            ->where('post_type', 'assignment')
                            ->orderBy('due_date', 'asc')
                            ->get();
                            
        $assignmentIds = $assignments->pluck('id');
        
        // Nếu có học sinh thì mới tìm bài nộp, nếu không thì trả về collection rỗng
        $submissions = ($studentCount > 0) 
            ? Submission::whereIn('post_id', $assignmentIds)
                        ->whereIn('user_id', $studentIds)
                        ->whereNotNull('grade')
                        ->with('post:id,max_points')
                        ->get()
            : collect([]); // Collection rỗng

        // Tính toán (Các hàm này đã an toàn với số 0)
        $chartGradeDistribution = $this->calculateGradeDistribution($submissions, $studentIds, $studentCount, $assignments);     
        $chartCompletionRate = $this->calculateCompletionRateChart($assignments, $submissions, $studentCount);
        $chartStudentPerformance = $this->calculateStudentPerformanceChart($submissions, $studentUsers, $assignments);

        return response()->json([
            'chartGradeDistribution'  => $chartGradeDistribution,
            'chartCompletionRate'     => $chartCompletionRate,
            'chartStudentPerformance' => $chartStudentPerformance,
        ]);
    }

    private function calculateGradeDistribution($submissions, $studentIds, $studentCount, $assignments)
    {
        $totalMaxPoints = $assignments->sum('max_points');

        // Trường hợp: Chưa có bài tập HOẶC Chưa có học sinh
        if ($totalMaxPoints == 0 || $studentCount == 0) {
            return [
                'labels' => ['0-20%', '20-40%', '40-60%', '60-80%', '80-100%'],
                'datasets' => [[
                    'label' => 'Tỉ lệ học sinh (%)',
                    'data' => [0, 0, 0, 0, 0], // Dữ liệu rỗng an toàn
                    'backgroundColor' => ['#FF6384', '#FF9F40', '#FFCD56', '#4BC0C0', '#36A2EB'],
                ]],
            ];
        }

        // ... Logic bucket cũ giữ nguyên ...
        // (Tôi rút gọn để code dễ nhìn, logic bucket của bạn ở trên đã đúng)
        $buckets = [
            'bucket1' => [0, $totalMaxPoints * 0.2],
            'bucket2' => [$totalMaxPoints * 0.2, $totalMaxPoints * 0.4],
            'bucket3' => [$totalMaxPoints * 0.4, $totalMaxPoints * 0.6],
            'bucket4' => [$totalMaxPoints * 0.6, $totalMaxPoints * 0.8],
            'bucket5' => [$totalMaxPoints * 0.8, $totalMaxPoints],
        ];

        $labels = [
            round($buckets['bucket1'][0]) . '-' . round($buckets['bucket1'][1]),
            round($buckets['bucket2'][0]) . '-' . round($buckets['bucket2'][1]),
            round($buckets['bucket3'][0]) . '-' . round($buckets['bucket3'][1]),
            round($buckets['bucket4'][0]) . '-' . round($buckets['bucket4'][1]),
            round($buckets['bucket5'][0]) . '-' . round($buckets['bucket5'][1]),
        ];

        $counts = ['bucket1' => 0, 'bucket2' => 0, 'bucket3' => 0, 'bucket4' => 0, 'bucket5' => 0];

        foreach ($studentIds as $studentId) {
            $totalScored = $submissions->where('user_id', $studentId)->sum('grade');
            if ($totalScored <= $buckets['bucket1'][1]) $counts['bucket1']++;
            elseif ($totalScored <= $buckets['bucket2'][1]) $counts['bucket2']++;
            elseif ($totalScored <= $buckets['bucket3'][1]) $counts['bucket3']++;
            elseif ($totalScored <= $buckets['bucket4'][1]) $counts['bucket4']++;
            else $counts['bucket5']++;
        }

        $data = [];
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
                'backgroundColor' => ['#FF6384', '#FF9F40', '#FFCD56', '#4BC0C0', '#36A2EB'],
            ]],
        ];
    }

    private function calculateCompletionRateChart($assignments, $submissions, $studentCount)
    {
        $labels = [];
        $completionRateData = [];

        foreach ($assignments as $assignment) {
            $labels[] = $assignment->title;
            // Nếu không có học sinh, tỷ lệ luôn là 0
            if ($studentCount == 0) {
                $completionRateData[] = 0;
                continue;
            }

            $submissionsForThisPost = $submissions->where('post_id', $assignment->id);
            $submissionCount = $submissionsForThisPost->count();
            
            // Tránh chia cho 0
            $rate = ($studentCount > 0) ? ($submissionCount / $studentCount) * 100 : 0;
            $completionRateData[] = round($rate, 2);
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => 'Tỷ lệ nộp bài (%)',
                'data' => $completionRateData,
                'backgroundColor' => 'rgba(75, 192, 192, 0.6)',
            ]],
        ];
    }

    private function calculateStudentPerformanceChart($submissions, $studentUsers, $assignments)
    {
        $totalClassMaxPoints = $assignments->sum('max_points');
        
        // Nếu không có bài tập nào hoặc không có học sinh -> Trả về rỗng
        if ($totalClassMaxPoints == 0 || $studentUsers->isEmpty()) {
             return ['labels' => [], 'datasets' => []];
        }

        $labels = []; 
        $studentAverageData = [];
        $studentAverages = []; 

        foreach ($studentUsers as $student) {
            $labels[] = $student->name;
            $studentTotalPoints = $submissions->where('user_id', $student->id)->sum('grade');
            $studentAvgPercent = ($studentTotalPoints / $totalClassMaxPoints) * 100;

            $roundedAvg = round($studentAvgPercent, 2);
            $studentAverageData[] = $roundedAvg;
            $studentAverages[] = $roundedAvg; 
        }

        $classAverage = (count($studentAverages) > 0) 
            ? round(array_sum($studentAverages) / count($studentAverages), 2)
            : 0;

        $classAverageLineData = array_fill(0, count($labels), $classAverage);

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'type' => 'bar', 
                    'label' => 'Điểm TB học sinh (%)',
                    'data' => $studentAverageData,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.6)',
                    'order' => 2 
                ],
                [
                    'type' => 'line', 
                    'label' => 'Điểm TB lớp (%)',
                    'data' => $classAverageLineData,
                    'borderColor' => 'rgba(255, 99, 132, 1)', 
                    'borderWidth' => 3,
                    'fill' => false,
                    'tension' => 0.1,
                    'order' => 1 
                ]
            ]
        ];
    }
}