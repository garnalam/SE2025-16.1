<?php

namespace App\Http\Controllers;

use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Events\StudentAttended;
use App\Notifications\AttendanceStartedNotification;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;
class AttendanceController extends Controller
{
    // 1. Tạo phiên điểm danh mới
    public function create(Request $request, Team $team)
    {
        $this->authorize('update', $team); // Chỉ GV

        // Đóng các phiên cũ của lớp này (nếu quên đóng)
        AttendanceSession::where('team_id', $team->id)->update(['is_active' => false]);

        $session = AttendanceSession::create([
            'team_id' => $team->id,
            'user_id' => Auth::id(),
        ]);

        $token = Str::random(6);
        Cache::put("attendance_token_{$session->id}", $token, 20); // Token sống 20s

       // Chỉ định rõ lấy role trong bảng trung gian (team_user)
        $students = $team->users()->where('team_user.role', 'student')->get();
        // Nếu role nằm ở bảng pivot team_user thì dùng: 
        // $students = $team->users()->wherePivot('role', 'student')->get();
        
        if ($students->count() > 0) {
            Notification::send($students, new AttendanceStartedNotification($session, $token));
        }

        return response()->json(['session_id' => $session->id, 'token' => $token]);
    }

    // 2. Làm mới Token (Mỗi 10s)
    public function refreshToken(AttendanceSession $session)
    {
        if (Auth::id() !== $session->user_id) abort(403);

        $newToken = Str::random(6);
        Cache::put("attendance_token_{$session->id}", $newToken, 15);

        return response()->json(['token' => $newToken]);
    }

    // 3. Học sinh điểm danh (Qua QR hoặc Link)
    public function joinByQr($sessionId, $token)
    {
        $session = AttendanceSession::findOrFail($sessionId);
        $validToken = Cache::get("attendance_token_{$sessionId}");

        if (!$validToken || $validToken !== $token) {
            return redirect()->route('dashboard')->with('error', 'Mã QR đã hết hạn!');
        }

        return $this->processAttendance($session);
    }

    // 4. Học sinh nhập mã tay
    public function joinByCode(Request $request)
    {
        $request->validate(['code' => 'required']);
        $user = Auth::user();
        
        // Tìm phiên đang mở của các lớp user tham gia
        $activeSession = AttendanceSession::whereHas('team', function($q) use ($user) {
            $q->whereIn('id', $user->teams->pluck('id'));
        })->where('is_active', true)->latest()->first();

        if (!$activeSession) return back()->with('error', 'Không tìm thấy phiên điểm danh nào.');

        $validToken = Cache::get("attendance_token_{$activeSession->id}");
        
        if ($request->code !== $validToken) {
             return back()->with('error', 'Mã sai hoặc hết hạn.');
        }

        return $this->processAttendance($activeSession);
    }

    // Hàm xử lý ghi danh
    private function processAttendance($session)
    {
        $user = Auth::user();
        
        // Check trùng
        if (AttendanceRecord::where('attendance_session_id', $session->id)->where('user_id', $user->id)->exists()) {
            return redirect()->route('dashboard')->with('info', 'Bạn đã điểm danh rồi.');
        }

        AttendanceRecord::create([
            'attendance_session_id' => $session->id,
            'user_id' => $user->id,
            'joined_at' => now(),
        ]);

        // Realtime cập nhật màn hình GV
        broadcast(new StudentAttended($session->id, $user))->toOthers();

        return redirect()->route('dashboard')->with('success', 'Điểm danh thành công! ✔️');
    }

    // 5. Kết thúc phiên
    public function close(AttendanceSession $session)
    {
        $session->update(['is_active' => false]);
        return back()->with('success', 'Đã đóng phiên điểm danh.');
    }

public function history(Team $team)
    {
        $this->authorize('view', $team); //
        
        // Lấy danh sách sessions sắp xếp theo ngày
        $sessions = $team->attendanceSessions()
            ->orderBy('created_at', 'desc') // Mới nhất lên đầu (hoặc asc tùy bạn)
            ->get();

        // Lấy danh sách học sinh và kèm theo dữ liệu điểm danh của họ
        // Eager loading 'attendanceRecords' để tránh query N+1
        $students = $team->users()
            ->where('team_user.role', 'student') // Chỉ lấy học sinh
            ->with(['attendanceRecords' => function($q) use ($sessions) {
                $q->whereIn('attendance_session_id', $sessions->pluck('id'));
            }])
            ->orderBy('name')
            ->get();

        // Kiểm tra quyền giáo viên (để hiển thị nút sửa bên Vue)
        $canEdit = Gate::allows('update', $team);

        return Inertia::render('Teams/AttendanceHistory', [
            'team' => $team,
            'sessions' => $sessions,
            'students' => $students,
            'canEdit' => $canEdit,
        ]);
    }

    // --- THÊM MỚI HÀM TOGGLE ---
    public function toggle(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:attendance_sessions,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $session = AttendanceSession::findOrFail($request->session_id);
        
        // Bảo mật: Kiểm tra xem người dùng hiện tại có quyền sửa team này không
        $this->authorize('update', $session->team);

        // Tìm record điểm danh
        $record = AttendanceRecord::where('attendance_session_id', $request->session_id)
            ->where('user_id', $request->user_id)
            ->first();

        if ($record) {
            // Nếu đã có -> Xóa đi (Chuyển thành Vắng/X đỏ)
            // Hoặc bạn có thể update status = 'absent' nếu muốn lưu lịch sử chi tiết
            $record->delete();
        } else {
            // Nếu chưa có -> Tạo mới (Chuyển thành Có mặt/Tích xanh)
            AttendanceRecord::create([
                'attendance_session_id' => $request->session_id,
                'user_id' => $request->user_id,
                'status' => 'present', //
                'joined_at' => now(),
            ]);
        }

        return back(); // Load lại trang để cập nhật UI
    }
}