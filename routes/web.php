<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\PostController;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Laravel\Jetstream\Jetstream;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\TopicController;
use App\Models\Topic; // <-- ĐÃ THÊM DÒNG NÀY
use App\Http\Controllers\PollVoteController;
use App\Http\Controllers\CommentController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    
    // ===== LOGIC DASHBOARD CỦA BẠN (GIỮ NGUYÊN) =====
    Route::get('/dashboard', function () {
        $user = auth()->user();
        
        if ($user->role === 'student') {
            return Inertia::render('StudentDashboard');
        } elseif ($user->role === 'teacher' || $user->role === 'admin') {
            // Giả sử teacher và admin dùng chung Dashboard
            return Inertia::render('Dashboard');
        }
        
        // Mặc định (ví dụ: cho vai trò không xác định)
        return Inertia::render('Dashboard');

    })->name('dashboard');

    // Route cho học sinh tham gia lớp học (GIỮ NGUYÊN)
    Route::post('/classrooms/join', [ClassroomController::class, 'join'])
        ->name('classrooms.join');


    // Route để TẠO bài đăng (GIỮ NGUYÊN - SẼ SỬA Ở BƯỚC SAU)
    Route::post('/topics/{topic}/posts', [PostController::class, 'store'])->name('posts.store');
    
    // ===== ROUTES QUẢN LÝ CHỦ ĐỀ (TOPIC) =====
    Route::post('/teams/{team}/topics', [TopicController::class, 'store'])->name('topics.store');
    
    // Cập nhật chủ đề (sẽ dùng ở bước sau)
    Route::put('/topics/{topic}', [TopicController::class, 'update'])->name('topics.update');

    // Xóa chủ đề
    Route::delete('/topics/{topic}', [TopicController::class, 'destroy'])->name('topics.destroy');

    // (QUAN TRỌNG) Route để xem các bài đăng BÊN TRONG chủ đề
    Route::get('/topics/{topic}', [TopicController::class, 'show'])->name('topics.show');

    Route::patch('/topics/{topic}/lock', [TopicController::class, 'toggleLock'])->name('topics.toggleLock');

    Route::post('/poll-votes/{pollOption}', [PollVoteController::class, 'store'])->name('poll-votes.store');

    // ===== ROUTES BÌNH LUẬN (MỚI) =====
// Tạo một bình luận mới (cho 1 bài đăng)
Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');

// ===== ROUTE CẬP NHẬT BÀI ĐĂNG (MỚI) =====
// Tắt/Mở bình luận cho 1 bài đăng
Route::patch('/posts/{post}/toggle-comments', [PostController::class, 'toggleComments'])->name('posts.toggleComments');
    // ===== KẾT THÚC ROUTES TOPIC =====


    // ===== ROUTE TRANG FEED (ĐÃ SỬA) =====
    // Trang này giờ sẽ hiển thị DANH SÁCH CHỦ ĐỀ (TOPICS)
    Route::get('/teams/{team}/feed', function (Team $team) {
        if (Gate::denies('view', $team)) {
            abort(403);
        }
        
        // Tải danh sách chủ đề (topics) thay vì bài đăng (posts)
        $team->load('topics.user'); // Eager load topics và người tạo

        return Inertia::render('Teams/Feed', [
            'team' => $team,
            'topics' => $team->topics, // <-- Truyền "topics"
            'permissions' => [
                // Giữ permission cũ
                'canAddTeamMembers' => Gate::check('addTeamMember', $team),
                
                // Thêm permission mới để kiểm tra ai được tạo chủ đề
                'canCreateTopics' => Gate::check('create', [Topic::class, $team]),
            ],
        ]);
    })->name('teams.feed');


    // ===== ROUTE CÀI ĐẶT LỚP HỌC (GIỮ NGUYÊN) =====
    Route::get('/teams/{team}', function (Team $team) { // <-- Thay đổi ở đây
        if (Gate::denies('view', $team)) {
            abort(403);
        }
        return Inertia::render('Teams/Show', [
            'team' => $team->load('owner', 'users', 'teamInvitations'),
            'availableRoles' => array_values(Jetstream::$roles),
            'availablePermissions' => Jetstream::$permissions,
            'defaultPermissions' => Jetstream::$defaultPermissions,
            'permissions' => [
                'canAddTeamMembers' => Gate::check('addTeamMember', $team),
                'canDeleteTeam' => Gate::check('delete', $team),
                'canRemoveTeamMembers' => Gate::check('removeTeamMember', $team),
                'canUpdateTeam' => Gate::check('update', $team),
                'canUpdateTeamMembers' => Gate::check('updateTeamMember', $team),
                'canInviteTeamMembers' => Gate::check('addTeamMember', $team),
            ],
        ]);
    })->name('teams.show');
});