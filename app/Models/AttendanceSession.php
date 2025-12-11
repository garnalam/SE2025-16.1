<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSession extends Model
{
    use HasFactory;

    protected $fillable = ['team_id', 'user_id', 'is_active', 'name']; // Thêm 'name' nếu bạn đã thêm cột này vào migration

    // Quan hệ ngược về Team
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    // Quan hệ với người tạo (Giáo viên)
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Quan hệ với chi tiết điểm danh (Records)
    public function records()
    {
        // Lưu ý: tên function là 'records' khớp với controller bạn đang dùng
        // trong controller: $team->attendanceSessions()->with('records')
        return $this->hasMany(AttendanceRecord::class, 'attendance_session_id');
    }
    
    // Alias cho records (để an toàn nếu controller gọi attendanceRecords)
    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class, 'attendance_session_id');
    }
}