<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Laravel\Jetstream\Jetstream;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Http\Controllers\AttendanceController;
// --- Imports Controllers ---
use App\Http\Controllers\PostController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\PostQuizController;
use App\Http\Controllers\Teacher\AnalyticsController;
use App\Http\Controllers\Teacher\DashboardController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\StudentClassroomController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\PollVoteController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\QuizAttemptController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\QuestionImportController;
use App\Http\Controllers\QuizTemplateController;

// --- Imports Models ---
use App\Models\Submission;
use App\Models\Team;
use App\Models\Topic;
use App\Models\Post;
use App\Models\QuizTemplate;

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

    // ===== 1. DASHBOARD & TÀI NGUYÊN CƠ BẢN =====
    Route::resource('subjects', SubjectController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('tags', TagController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::get('/dashboard', function () {
        $user = Auth::user();
        $currentTeam = $user->currentTeam;

        // Xác định quyền hạn (Teacher?)
        $isTeacher = false;
        if ($user->role === 'admin') {
            $isTeacher = true;
        } elseif ($currentTeam) {
            if ($user->ownsTeam($currentTeam) || $user->hasTeamRole($currentTeam, 'teacher')) {
                $isTeacher = true;
            }
        }

        if ($isTeacher) {
            return app(DashboardController::class)->index();
        } else {
            // Logic Dashboard Học sinh
            $teamIds = $user->teams()->pluck('teams.id');
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
                $averagePercent = ($totalMaxPoints > 0) ? ($totalPoints / $totalMaxPoints) * 100 : 0;

                $chartLabels[] = $team->name;
                $chartData[] = round($averagePercent, 2);
            }

            $progressChartData = [
                'labels' => $chartLabels,
                'datasets' => [[
                    'label'           => 'Điểm trung bình (%)',
                    'backgroundColor' => '#4CAF50',
                    'data'            => $chartData,
                    'borderRadius'    => 5,
                ]]
            ];

            $upcomingAssignments = Post::where('post_type', 'assignment')
                ->whereIn('team_id', $teamIds)
                ->where('due_date', '>=', Carbon::now())
                ->where('due_date', '<=', Carbon::now()->addDays(7))
                ->whereDoesntHave('submissions', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with('team:id,name')
                ->orderBy('due_date', 'asc')
                ->limit(5)
                ->get();

            $latestAnnouncements = Post::whereIn('team_id', $teamIds)
                ->whereHas('topic', function ($query) {
                    $query->where('is_locked', true);
                })
                ->with('team:id,name')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            return Inertia::render('StudentDashboard', [
                'progressChartData'   => $progressChartData,
                'upcomingAssignments' => $upcomingAssignments,
                'latestAnnouncements' => $latestAnnouncements,
            ]);
        }
    })->name('dashboard');

    // ===== 2. THÔNG BÁO (NOTIFICATIONS) - KHỚP VỚI VUE =====
    // Lấy danh sách thông báo
    Route::get('/notifications', function (Request $request) {
        return $request->user()->notifications()->take(20)->get();
    })->name('notifications.index');

    // Đánh dấu 1 thông báo là đã đọc
    Route::post('/notifications/{id}/read', function (Request $request, $id) {
        $notification = $request->user()->notifications()->find($id);
        if ($notification) {
            $notification->markAsRead();
        }
        return response()->noContent();
    });

    // ===== 3. QUẢN LÝ LỚP HỌC & CHỦ ĐỀ =====
    Route::post('/classrooms/join', [StudentClassroomController::class, 'join'])->name('classrooms.join');

    // Xem trang Feed lớp học
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
    // --- ROUTES ĐIỂM DANH QR ---
    Route::post('/teams/{team}/attendance/create', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/attendance/{session}/refresh', [AttendanceController::class, 'refreshToken'])->name('attendance.refresh');
    Route::post('/attendance/{session}/close', [AttendanceController::class, 'close'])->name('attendance.close');

    // Route xử lý học sinh tham gia
    Route::get('/attendance/{session}/{token}', [AttendanceController::class, 'joinByQr'])->name('attendance.join');
    Route::post('/attendance/join-code', [AttendanceController::class, 'joinByCode'])->name('attendance.join-code');

    // Thêm vào dưới route attendance.history
Route::post('/attendance/toggle', [AttendanceController::class, 'toggle'])->name('attendance.toggle');

    // Route xem lịch sử
    Route::get('/teams/{team}/attendance-history', [AttendanceController::class, 'history'])->name('attendance.history');
    // Cài đặt lớp học (Jetstream)
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

    // Topics Routes
    Route::post('/teams/{team}/topics', [TopicController::class, 'store'])->name('topics.store');
    Route::put('/topics/{topic}', [TopicController::class, 'update'])->name('topics.update');
    Route::delete('/topics/{topic}', [TopicController::class, 'destroy'])->name('topics.destroy');
    Route::get('/topics/{topic}', [TopicController::class, 'show'])->name('topics.show');
    Route::patch('/topics/{topic}/lock', [TopicController::class, 'toggleLock'])->name('topics.toggleLock');
    Route::post('/poll-votes/{pollOption}', [PollVoteController::class, 'store'])->name('poll-votes.store');

    // ===== 4. BÀI ĐĂNG (POSTS) & BÌNH LUẬN =====
    Route::post('/topics/{topic}/posts', [PostController::class, 'store'])->name('posts.store');
    Route::patch('/posts/{post}/toggle-comments', [PostController::class, 'toggleComments'])->name('posts.toggleComments');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');

    // ===== 5. NGÂN HÀNG CÂU HỎI & IMPORT =====
    Route::get('/questions/import', [QuestionImportController::class, 'create'])->name('questions.import.create');
    Route::post('/questions/import', [QuestionImportController::class, 'store'])->name('questions.import.store');
    Route::get('/questions/import/template', [QuestionImportController::class, 'downloadTemplate'])->name('questions.import.template');
    Route::resource('questions', QuestionController::class);

        // [THÊM MỚI] Route xử lý AI
    Route::post('/questions/generate-ai', [QuestionController::class, 'generateAiQuestions'])->name('questions.generate-ai');
    Route::post('/questions/store-bulk', [QuestionController::class, 'storeBulk'])->name('questions.store-bulk');

    // ===== 6. QUIZ (BÀI KIỂM TRA) =====
    // Quản lý đề thi
    Route::get('/posts/{post}/quiz/manage', [PostQuizController::class, 'manage'])->name('post.quiz.manage');
    Route::post('/posts/{post}/quiz/attach', [PostQuizController::class, 'attach'])->name('post.quiz.attach');
    Route::delete('/posts/{post}/quiz/detach', [PostQuizController::class, 'detach'])->name('post.quiz.detach');
    Route::post('/posts/{post}/quiz/generate', [PostQuizController::class, 'generate'])->name('post.quiz.generate');
    Route::post('/posts/{post}/quiz/save-manual', [PostQuizController::class, 'saveManualSettings'])->name('post.quiz.saveManual');
    Route::resource('quiz-templates', QuizTemplateController::class)->only(['store', 'destroy']);

    // Làm bài thi (Attempts)
    Route::post('/posts/{post}/quiz/start', [QuizAttemptController::class, 'start'])->name('quiz.start');
    Route::get('/attempt/{attempt}/question/{questionNumber}', [QuizAttemptController::class, 'showQuestion'])->name('quiz.question.show');
    Route::post('/attempt/{attempt}/question/{questionNumber}', [QuizAttemptController::class, 'saveAnswer'])->name('quiz.question.save');
    Route::get('/attempt/{attempt}/submit', [QuizAttemptController::class, 'submitPage'])->name('quiz.submitPage');
    Route::post('/attempt/{attempt}/submit', [QuizAttemptController::class, 'finishAttempt'])->name('quiz.finish');
    Route::get('/attempt/{attempt}/results', [QuizAttemptController::class, 'showResults'])->name('quiz.results');
    Route::post('/quiz-attempts/{attempt}/log-violation', [QuizAttemptController::class, 'logViolation'])->name('quiz.log-violation');
});

// ===== 7. NỘP BÀI & CHẤM ĐIỂM (SUBMISSIONS) =====
// Cần auth nhưng nằm ngoài nhóm verified (hoặc trong tùy logic)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/submissions/{submission}/ai-grade', [SubmissionController::class, 'requestAiGrading'])->name('submissions.ai-grade');
    Route::post('/posts/{post}/submit', [SubmissionController::class, 'store'])->name('submissions.store');
    Route::get('/analytics/class/{team}', [AnalyticsController::class, 'show'])->name('analytics.class.show');
    Route::get('/posts/{post}/submissions', [SubmissionController::class, 'index'])->name('submissions.index');
    Route::put('/submissions/{submission}/grade', [SubmissionController::class, 'grade'])->name('submissions.grade');
    Route::get('/submissions/file/{submission_file}', [SubmissionController::class, 'downloadFile'])->name('submissions.downloadFile');
});