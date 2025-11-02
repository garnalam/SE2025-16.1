<?php
use App\Http\Controllers\PostController;
use App\Models\Submission;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
// use App\Http\Controllers\PostController; // <-- Đã di chuyển khỏi đây
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Laravel\Jetstream\Jetstream;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\StudentClassroomController; 
use App\Http\Controllers\TopicController;
use App\Http\Controllers\PollVoteController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\SubmissionController; 
use App\Models\Topic; 
use App\Models\Post;
use Illuminate\Support\Facades\Auth; // <-- Đã di chuyển lên đây
use Carbon\Carbon; // <-- Đã di chuyển lên đây
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
    
    // ===== LOGIC DASHBOARD =====
    Route::get('/dashboard', function () {
        $user = Auth::user(); // Dùng Auth::user()
        
        if ($user->role === 'student') {
            
            // Lấy ID các lớp học
            $teamIds = $user->teams()->pluck('teams.id');

            // 1. LẤY DỮ LIỆU BIỂU ĐỒ
            $studentTeams = $user->teams()->get(['teams.id', 'teams.name']);
            $chartLabels = [];
            $chartData = [];
            foreach ($studentTeams as $team) {
                $submissions = Submission::where('user_id', $user->id)
                    ->whereNotNull('grade')
                    ->whereHas('post', function ($query) use ($team) {
                        $query->where('team_id', $team->id);
                    })
                    ->with('post:id,max_points')
                    ->get();
                $totalPoints = $submissions->sum('grade');
                $totalMaxPoints = $submissions->sum('post.max_points');
                $averagePercent = ($totalMaxPoints > 0) 
                                    ? ($totalPoints / $totalMaxPoints) * 100 
                                    : 0;
                $chartLabels[] = $team->name;
                $chartData[] = round($averagePercent, 2);
            }
            $progressChartData = [
                'labels' => $chartLabels,
                'datasets' => [
                    [
                        'label'           => 'Điểm trung bình (%)',
                        'backgroundColor' => '#4CAF50',
                        'data'            => $chartData,
                        'borderRadius'    => 5,
                    ]
                ]
            ];

            // 2. Lấy Bài tập sắp đến hạn (Dùng Carbon)
            
            // ===== ĐÃ DỌN DẸP DEBUG =====
            $upcomingAssignments = Post::where('post_type', 'assignment')
                ->whereIn('team_id', $teamIds)
                ->where('due_date', '>=', Carbon::now()) // <-- Đã kích hoạt lại
                ->where('due_date', '<=', Carbon::now()->addDays(7)) // <-- Đã kích hoạt lại
                ->whereDoesntHave('submissions', function ($query) use ($user) {
                    $query->where('user_id', $user->id); // Chỉ lấy bài chưa nộp
                })
                ->with('team:id,name')
                ->orderBy('due_date', 'asc')
                ->limit(5)
                ->get();
            // ===== KẾT THÚC DỌN DẸP =====


            // 3. Lấy Thông báo mới nhất (từ các topic bị khóa)
            $latestAnnouncements = Post::whereIn('team_id', $teamIds)
                ->whereHas('topic', function ($query) {
                    $query->where('is_locked', true);
                })
                ->with('team:id,name')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            // 4. TRẢ VỀ VIEW VỚI DỮ LIỆU MỚI
            return Inertia::render('StudentDashboard', [
                'progressChartData'   => $progressChartData,
                'upcomingAssignments' => $upcomingAssignments,
                'latestAnnouncements' => $latestAnnouncements,
            ]);

        } elseif ($user->role === 'teacher' || $user->role === 'admin') {
            return Inertia::render('Dashboard');
        }
        
        return Inertia::render('Dashboard');

    })->name('dashboard');

    // Route cho học sinh tham gia lớp học
    Route::post('/classrooms/join', [StudentClassroomController::class, 'join'])
        ->name('classrooms.join');


    // ===== ROUTES BÀI ĐĂNG (POSTS) =====
    // Tạo bài đăng
    Route::post('/topics/{topic}/posts', [PostController::class, 'store'])->name('posts.store');
    // Bật/tắt bình luận
    Route::patch('/posts/{post}/toggle-comments', [PostController::class, 'toggleComments'])->name('posts.toggleComments');
    
    // --- THÊM CÁC ROUTE MỚI CHO SỬA/XÓA ---
    // Sửa bài đăng (Modal gọi)
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    // Xóa bài đăng (Nút 3 chấm gọi)
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    // -------------------------------------

    
    // ===== ROUTES QUẢN LÝ CHỦ ĐỀ (TOPIC) =====
    Route::post('/teams/{team}/topics', [TopicController::class, 'store'])->name('topics.store');
    Route::put('/topics/{topic}', [TopicController::class, 'update'])->name('topics.update');
    Route::delete('/topics/{topic}', [TopicController::class, 'destroy'])->name('topics.destroy');
    Route::get('/topics/{topic}', [TopicController::class, 'show'])->name('topics.show');
    Route::patch('/topics/{topic}/lock', [TopicController::class, 'toggleLock'])->name('topics.toggleLock');
    Route::post('/poll-votes/{pollOption}', [PollVoteController::class, 'store'])->name('poll-votes.store');

    // ===== ROUTES BÌNH LUẬN (COMMENTS) =====
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');


    // ===== ROUTE TRANG FEED (ĐÃ SỬA) =====
    Route::get('/teams/{team}/feed', function (Team $team) {
        if (Gate::denies('view', $team)) {
            abort(403);
        }
        
        $team->load('topics.user'); 

        return Inertia::render('Teams/Feed', [
            'team' => $team,
            'topics' => $team->topics,
            'permissions' => [
                'canAddTeamMembers' => Gate::check('addTeamMember', $team),
                'canCreateTopics' => Gate::check('create', [Topic::class, $team]),
            ],
        ]);
    })->name('teams.feed');


    // ===== ROUTE CÀI ĐẶT LỚP HỌC (GIỮ NGUYÊN) =====
    Route::get('/teams/{team}', function (Team $team) {
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

// ===== ROUTES NỘP BÀI (SUBMISSIONS) =====
// (Nằm ngoài group 'verified' để có thể truy cập, nhưng vẫn cần 'auth')
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/posts/{post}/submit', [SubmissionController::class, 'store'])
        ->name('submissions.store');

    Route::get('/posts/{post}/submissions', [SubmissionController::class, 'index'])
        ->name('submissions.index');

    Route::put('/submissions/{submission}/grade', [SubmissionController::class, 'grade'])
        ->name('submissions.grade');
        
    Route::get('/submissions/file/{submission_file}', [SubmissionController::class, 'downloadFile'])
        ->name('submissions.downloadFile');
});

