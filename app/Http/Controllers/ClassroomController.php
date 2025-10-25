<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Gate;
use Laravel\Jetstream\Jetstream;
use App\Models\Team; // Đảm bảo bạn đã import model Team (mặc định của Jetstream)

class ClassroomController extends Controller
{
    /**
     * Cho phép người dùng (học sinh) tham gia vào một lớp học bằng mã code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function join(Request $request)
    {
        // 1. Xác thực dữ liệu đầu vào
        $request->validate([
            'join_code' => ['required', 'string', 'exists:teams,join_code'],
        ], [
            'join_code.exists' => 'Mã lớp học không tồn tại hoặc không chính xác.'
        ]);

        // 2. Tìm lớp học (Team) bằng join_code
        // Sử dụng 'first' vì join_code có thể không unique (mặc dù nên là unique)
        $team = Team::where('join_code', $request->join_code)->first();

        // 3. Lấy người dùng hiện tại (học sinh)
        $user = $request->user();

        // 4. Kiểm tra xem người dùng đã ở trong lớp này chưa
        if ($team->hasUser($user)) {
            return Redirect::back()->withErrors(['join_code' => 'Bạn đã ở trong lớp học này.']);
        }
        
        // 5. Kiểm tra vai trò của người dùng (chỉ học sinh mới được tham gia)
        if ($user->role !== 'student') {
             return Redirect::back()->withErrors(['join_code' => 'Chức năng này chỉ dành cho học sinh.']);
        }

        // 6. Thêm học sinh vào lớp học (Team)
        // Lấy vai trò mặc định cho thành viên từ config của Jetstream
        $defaultRole = config('jetstream.roles.0.key', 'editor'); // 'editor' là giá trị dự phòng

        $team->users()->attach($user, ['role' => $defaultRole]);

        // 7. Tự động chuyển người dùng sang lớp học vừa tham gia
        if ($user->switchTeam($team)) {
            // Chuyển hướng đến dashboard (sẽ tự động là StudentDashboard)
            return Redirect::route('dashboard')->with('status', 'Tham gia lớp học thành công!');
        }

        // Nếu có lỗi khi chuyển team (ít khi xảy ra)
        return Redirect::back()->withErrors(['join_code' => 'Tham gia thành công nhưng không thể chuyển sang lớp học.']);
    }
}

