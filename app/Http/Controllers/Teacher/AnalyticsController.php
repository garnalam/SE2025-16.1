<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Team; // Lớp học
use App\Models\Post; // Bài tập
use App\Models\Submission; // Bài nộp
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Trả về dữ liệu phân tích cho một lớp học cụ thể.
     */
    public function show(Team $team)
    {
        // 1. Xác thực (Giữ nguyên)
        if (auth()->id() !== $team->user_id) {
            abort(403, 'Bạn không có quyền xem lớp này.');
        }
    
        // 2. Lấy dữ liệu cơ bản (Giữ nguyên)
        $studentUsers = $team->users()->where('users.role', 'student')->get(['users.id', 'users.name']);
        $studentIds = $studentUsers->pluck('id');
        $studentCount = $studentIds->count();
        if ($studentCount === 0) {
            return response()->json(['message' => 'Lớp học chưa có học sinh.'], 404);
        }
        $assignments = Post::where('team_id', $team->id)
                           ->where('post_type', 'assignment')
                           ->orderBy('due_date', 'asc')
                           ->get();
        $assignmentIds = $assignments->pluck('id');
        $submissions = Submission::whereIn('post_id', $assignmentIds)
                                 ->whereIn('user_id', $studentIds)
                                 ->whereNotNull('grade')
                                 ->with('post:id,max_points')
                                 ->get();
    
        // 3. Tính toán 3 biểu đồ (Sửa lại)
        $chartGradeDistribution = $this->calculateGradeDistribution($submissions, $studentIds, $studentCount, $assignments);    
        // V↓↓↓ SỬA LẠI CÁC DÒNG GỌI HÀM V↓↓↓
        $chartCompletionRate = $this->calculateCompletionRateChart($assignments, $submissions, $studentCount);
        $chartStudentPerformance = $this->calculateStudentPerformanceChart($submissions, $studentUsers, $assignments);
        // V↑↑↑ SỬA LẠI CÁC DÒNG GỌI HÀM V↑↑↑
    
        // 4. Trả về JSON (Sửa lại)
        // V↓↓↓ SỬA LẠI KHỐI RETURN NÀY V↓↓↓
        return response()->json([
            'chartGradeDistribution'  => $chartGradeDistribution,
            'chartCompletionRate'     => $chartCompletionRate,
            'chartStudentPerformance' => $chartStudentPerformance, // <-- Tên biểu đồ mới
        ]);
        // V↑↑↑ SỬA LẠI KHỐI RETURN NÀY V↑↑↑
    }

/**
 * Ý TƯỞNG 1: BIỂU ĐỒ PHÂN PHỐI ĐIỂM
 */
private function calculateGradeDistribution($submissions, $studentIds, $studentCount, $assignments)
{
    // 1. Tính tổng điểm tối đa của tất cả bài tập trong lớp
    $totalMaxPoints = $assignments->sum('max_points');

    // Nếu không có bài tập (hoặc chưa có điểm), trả về biểu đồ rỗng
    if ($totalMaxPoints == 0) {
        $labels = ['0-20', '20-40', '40-60', '60-80', '80-100']; // Nhãn mặc định
        $data = [0, 0, 0, 0, 0];
    } else {
        // 2. Định nghĩa các "thùng" (buckets) dựa trên % CỦA TỔNG ĐIỂM
        // Ví dụ: Nếu tổng điểm là 500, 20% là 100 điểm.
        $buckets = [
            'bucket1' => [0, $totalMaxPoints * 0.2],
            'bucket2' => [$totalMaxPoints * 0.2, $totalMaxPoints * 0.4],
            'bucket3' => [$totalMaxPoints * 0.4, $totalMaxPoints * 0.6],
            'bucket4' => [$totalMaxPoints * 0.6, $totalMaxPoints * 0.8],
            'bucket5' => [$totalMaxPoints * 0.8, $totalMaxPoints],
        ];

        // 3. Tạo nhãn (Trục Hoành - X-axis) dựa trên ĐIỂM SỐ thực tế
        $labels = [
            // Làm tròn cho đẹp, ví dụ: "0 - 100 điểm"
            round($buckets['bucket1'][0]) . ' - ' . round($buckets['bucket1'][1]) . ' điểm',
            round($buckets['bucket2'][0] + 0.01) . ' - ' . round($buckets['bucket2'][1]) . ' điểm',
            round($buckets['bucket3'][0] + 0.01) . ' - ' . round($buckets['bucket3'][1]) . ' đ',
            round($buckets['bucket4'][0] + 0.01) . ' - ' . round($buckets['bucket4'][1]) . ' đ',
            round($buckets['bucket5'][0] + 0.01) . ' - ' . round($buckets['bucket5'][1]) . ' đ',
        ];

        // 4. Khởi tạo bộ đếm
        $counts = [
            'bucket1' => 0, 'bucket2' => 0, 'bucket3' => 0, 'bucket4' => 0, 'bucket5' => 0,
        ];

        // 5. Đếm số học sinh rơi vào mỗi "thùng"
        foreach ($studentIds as $studentId) {
            // Tính TỔNG ĐIỂM THỰC TẾ của học sinh này
            $totalScored = $submissions->where('user_id', $studentId)->sum('grade');

            // Xếp học sinh vào thùng dựa trên TỔNG ĐIỂM
            if ($totalScored <= $buckets['bucket1'][1]) $counts['bucket1']++;
            elseif ($totalScored <= $buckets['bucket2'][1]) $counts['bucket2']++;
            elseif ($totalScored <= $buckets['bucket3'][1]) $counts['bucket3']++;
            elseif ($totalScored <= $buckets['bucket4'][1]) $counts['bucket4']++;
            else $counts['bucket5']++;
        }

        // 6. Chuyển đổi SỐ LƯỢNG đếm được sang TỈ LỆ % (Trục Tung - Y-axis)
        $data = [];
        foreach ($counts as $count) {
            $percentage = ($studentCount > 0) ? round(($count / $studentCount) * 100, 2) : 0;
            $data[] = $percentage;
        }
    }

    return [
        'labels' => $labels, // Trục X: Khoảng điểm (ví dụ: 0 - 100 điểm)
        'datasets' => [[
            'label' => 'Tỉ lệ học sinh (%)', // Tên chú thích
            'data' => $data, // Trục Y: Tỉ lệ %
            'backgroundColor' => ['#FF6384', '#FF9F40', '#FFCD56', '#4BC0C0', '#36A2EB'],
        ]],
    ];
}

    /**
     * Ý TƯỞNG 2 & 3: TỶ LỆ HOÀN THÀNH & ĐIỂM TRUNG BÌNH
     */
/**
 * Ý TƯỞNG 2: TỶ LỆ HOÀN THÀNH (Đã đơn giản hóa)
 */
private function calculateCompletionRateChart($assignments, $submissions, $studentCount)
{
    $labels = [];
    $completionRateData = [];

    foreach ($assignments as $assignment) {
        $labels[] = $assignment->title;

        // Tính tỷ lệ hoàn thành
        $submissionsForThisPost = $submissions->where('post_id', $assignment->id);
        $submissionCount = $submissionsForThisPost->count();

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

/**
 * Ý TƯỞNG 4 (MỚI): BIỂU ĐỒ SO SÁNH HIỆU SUẤT HỌC SINH
 */
private function calculateStudentPerformanceChart($submissions, $studentUsers, $assignments)
{
    // Tính tổng điểm tối đa của cả lớp (tất cả bài tập)
    $totalClassMaxPoints = $assignments->sum('max_points');
    if ($totalClassMaxPoints == 0) {
         // Tránh chia cho 0 nếu chưa có bài tập nào
         return ['labels' => [], 'datasets' => []];
    }

    $labels = []; // Tên học sinh
    $studentAverageData = []; // Mảng chứa điểm TB của từng học sinh
    $studentAverages = []; // Mảng tạm để tính TB lớp

    foreach ($studentUsers as $student) {
        $labels[] = $student->name;

        // Lấy tổng điểm mà học sinh này đạt được
        $studentTotalPoints = $submissions->where('user_id', $student->id)->sum('grade');

        // Tính điểm % trung bình chung cuộc của học sinh
        // (Nếu không nộp bài nào, điểm sẽ là 0 / tổng điểm, kéo TB xuống)
        $studentAvgPercent = ($studentTotalPoints / $totalClassMaxPoints) * 100;

        $roundedAvg = round($studentAvgPercent, 2);
        $studentAverageData[] = $roundedAvg;
        $studentAverages[] = $roundedAvg; // Thêm vào mảng tạm
    }

    // Tính điểm trung bình của cả lớp (là trung bình của các cột)
    $classAverage = (count($studentAverages) > 0) 
        ? round(array_sum($studentAverages) / count($studentAverages), 2)
        : 0;

    // Tạo một mảng chứa giá trị TB lớp, lặp lại cho mỗi học sinh
    $classAverageLineData = array_fill(0, count($labels), $classAverage);

    return [
        'labels' => $labels,
        'datasets' => [
            // Bộ dữ liệu 1: Biểu đồ cột
            [
                'type' => 'bar', // Quan trọng
                'label' => 'Điểm TB học sinh (%)',
                'data' => $studentAverageData,
                'backgroundColor' => 'rgba(75, 192, 192, 0.6)',
                'order' => 2 // Đảm bảo cột vẽ sau
            ],
            // Bộ dữ liệu 2: Biểu đồ đường
            [
                'type' => 'line', // Quan trọng
                'label' => 'Điểm TB lớp (%)',
                'data' => $classAverageLineData,
                'borderColor' => 'rgba(255, 99, 132, 1)', // Màu đỏ
                'borderWidth' => 3,
                'fill' => false,
                'tension' => 0.1,
                'order' => 1 // Đảm bảo đường vẽ trước
            ]
        ]
    ];
}
}