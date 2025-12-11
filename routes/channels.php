<?php
    
use Illuminate\Support\Facades\Broadcast;

// Định nghĩa quyền cho kênh App.Models.User.{id}
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    // Chỉ cho phép nghe nếu ID của user đang đăng nhập trùng với ID của kênh
    return (int) $user->id === (int) $id;
});
Broadcast::channel('attendance.{sessionId}', function ($user, $sessionId) {
    return true; // Ai đăng nhập rồi cũng được nghe (bạn có thể check kỹ hơn nếu muốn)
});