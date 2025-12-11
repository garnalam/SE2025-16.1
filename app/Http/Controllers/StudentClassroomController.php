<?php

namespace App\Http\Controllers;

use App\Models\Team; // <-- Import model Team (chính là Lớp học)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <-- Import Auth để lấy user

class StudentClassroomController extends Controller
{
    /**
     * Cho phép học sinh (student) tham gia vào một Lớp học (Team)
     * bằng mã tham gia (join_code).
     */
    public function join(Request $request)
    {
        // 1. Xác thực dữ liệu gửi lên (phải có join_code)
        $request->validate([
            'join_code' => 'required|string',
        ]);

        $student = Auth::user(); // Lấy học sinh đang đăng nhập
        $joinCode = $request->input('join_code');

        // 2. Tìm Lớp học (Team) có mã join_code này
        $team = Team::where('join_code', $joinCode)->first();

        // 3. XỬ LÝ LỖI: Nếu không tìm thấy lớp
        if (!$team) {
            return back()->withErrors([
                'join_code' => 'Mã lớp học không tồn tại hoặc không chính xác.'
            ]);
        }

        // 4. XỬ LÝ LỖI: Nếu học sinh đã ở trong lớp
        if ($student->belongsToTeam($team)) {
            return back()->withErrors([
                'join_code' => 'Bạn đã tham gia lớp học này rồi.'
            ]);
        }

        // 5. THÊM HỌC SINH VÀO LỚP:
        $student->teams()->attach($team, ['role' => 'student']);
        // ===== THÊM MỚI (FIX LỖI) =====
        // Tự động chuyển học sinh sang lớp học này
        // nếu họ chưa có lớp học hiện tại (current_team_id == null)
        if (is_null($student->current_team_id)) {
            // switchTeam() là một hàm của Jetstream
            // nó sẽ cập nhật `current_team_id` trong bảng `users`
            $student->switchTeam($team);
        }
        // ===== KẾT THÚC THÊM MỚI =====

        // 6. THÀNH CÔNG: Chuyển hướng lại
        return redirect()->back()->with('status', 'Bạn đã tham gia lớp học thành công!');
    }
}
