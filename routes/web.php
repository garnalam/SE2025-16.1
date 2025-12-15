<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB; // Thêm DB để tính toán
use Illuminate\Http\Request;
use Laravel\Jetstream\Jetstream;
use Inertia\Inertia;
use Carbon\Carbon;

// --- Imports Controllers ---
use App\Http\Controllers\AttendanceController;
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
})->name('welcome');

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
            // --- LOGIC DASHBOARD HỌC SINH (DATA THẬT) ---
            $currentTeamId = $user->current_team_id;

            // Nếu chưa chọn lớp, trả về rỗng
            if (!$currentTeamId) {
                return Inertia::render('StudentDashboard', [
                    'taskCompletionData' => null,
                    'performanceChartData' => null,
                    'quizAnalyticsData' => null,
                    'upcomingAssignments' => [],
                    'latestAnnouncements' => [],
                ]);
            }

            // ====================================================
            // 1. CHART: TASK COMPLETION (Đã nộp / Chờ / Quá hạn)
            // ====================================================
            // Lấy tất cả bài tập/quiz của lớp hiện tại
            $allAssignments = Post::where('team_id', $currentTeamId)
                ->whereIn('post_type', ['assignment', 'quiz'])
                ->get();

            // Lấy các bài đã nộp của user này
            $mySubmissions = Submission::where('user_id', $user->id)
                ->whereIn('post_id', $allAssignments->pluck('id'))
                ->pluck('post_id')
                ->toArray();

            $completedCount = count($mySubmissions);
            $pendingCount = 0;
            $overdueCount = 0;
            $now = Carbon::now();

            foreach ($allAssignments as $post) {
                // Nếu chưa nộp
                if (!in_array($post->id, $mySubmissions)) {
                    if ($post->due_date && $now->gt($post->due_date)) {
                        $overdueCount++;
                    } else {
                        $pendingCount++;
                    }
                }
            }

            $taskCompletionData = [
                'labels' => ['Đã nộp', 'Đang chờ', 'Quá hạn'],
                'datasets' => [[
                    'backgroundColor' => ['#10b981', '#3b82f6', '#f43f5e'], // Emerald, Blue, Rose
                    'borderWidth' => 0,
                    'data' => [$completedCount, $pendingCount, $overdueCount]
                ]]
            ];

            // ====================================================
            // 2. CHART: PERFORMANCE TRAJECTORY (Lịch sử điểm số)
            // ====================================================
            // Lấy 10 bài nộp gần nhất đã được chấm điểm
            $gradedSubmissions = Submission::where('user_id', $user->id)
                ->whereNotNull('grade')
                ->whereHas('post', function ($q) use ($currentTeamId) {
                    $q->where('team_id', $currentTeamId);
                })
                ->with('post:id,title,max_points')
                ->latest('updated_at') // Lấy theo thời gian chấm gần nhất
                ->take(10)
                ->get()
                ->reverse() // Đảo ngược để hiển thị cũ -> mới trên biểu đồ
                ->values();

            $performanceLabels = $gradedSubmissions->map(fn($sub) => \Illuminate\Support\Str::limit($sub->post->title, 10));
            
            // Quy đổi ra thang điểm 100 để vẽ biểu đồ cho đều
            $performanceData = $gradedSubmissions->map(function($sub) {
                if ($sub->post->max_points > 0) {
                    return round(($sub->grade / $sub->post->max_points) * 100, 1);
                }
                return 0;
            });

            $performanceChartData = [
                'labels' => $performanceLabels,
                'datasets' => [[
                    'label' => 'Hiệu suất (%)',
                    'borderColor' => '#8b5cf6', // Violet
                    'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                    'data' => $performanceData,
                    'fill' => true,
                    'tension' => 0.4,
                    'pointBackgroundColor' => '#8b5cf6',
                    'pointBorderColor' => '#fff',
                ]]
            ];

            // ====================================================
            // 3. CHART: QUIZ ANALYTICS (Tôi vs Trung bình lớp)
            // ====================================================
            // Lấy 5 bài Quiz gần nhất của lớp
            $recentQuizzes = Post::where('team_id', $currentTeamId)
                ->where('post_type', 'quiz')
                ->latest()
                ->take(5)
                ->get()
                ->reverse(); // Cũ -> Mới

            $quizLabels = [];
            $myScores = [];
            $classAvgScores = [];

            foreach ($recentQuizzes as $quiz) {
                $quizLabels[] = \Illuminate\Support\Str::limit($quiz->title, 10);
                
                // Điểm của tôi (quy đổi %)
                $mySub = Submission::where('user_id', $user->id)->where('post_id', $quiz->id)->first();
                $myScoreVal = ($mySub && $quiz->max_points > 0) ? round(($mySub->grade / $quiz->max_points) * 100) : 0;
                $myScores[] = $myScoreVal;

                // Điểm trung bình lớp (quy đổi %)
                $avgGrade = Submission::where('post_id', $quiz->id)->whereNotNull('grade')->avg('grade');
                $avgScoreVal = ($avgGrade && $quiz->max_points > 0) ? round(($avgGrade / $quiz->max_points) * 100) : 0;
                $classAvgScores[] = $avgScoreVal;
            }

            $quizAnalyticsData = [
                'labels' => $quizLabels,
                'datasets' => [
                    [
                        'label' => 'Tôi',
                        'backgroundColor' => '#06b6d4', // Cyan
                        'data' => $myScores,
                        'borderRadius' => 4,
                        'barPercentage' => 0.6,
                    ],
                    [
                        'label' => 'TB Lớp',
                        'backgroundColor' => '#64748b', // Slate
                        'data' => $classAvgScores,
                        'borderRadius' => 4,
                        'barPercentage' => 0.6,
                    ]
                ]
            ];

            // ====================================================
            // DỮ LIỆU DANH SÁCH (Giữ nguyên logic cũ)
            // ====================================================
            $upcomingAssignments = Post::where('post_type', 'assignment')
                ->where('team_id', $currentTeamId)
                ->where('due_date', '>=', Carbon::now())
                ->whereDoesntHave('submissions', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->with('team:id,name')
                ->orderBy('due_date', 'asc')
                ->limit(5)
                ->get();

            $latestAnnouncements = Post::query()
                ->where('team_id', $currentTeamId)
                ->where('post_type', 'text')
                ->whereHas('user', function ($q) {
                    $q->where('role', 'teacher');
                })
                ->with(['user', 'team'])
                ->latest()
                ->limit(10)
                ->get();

            return Inertia::render('StudentDashboard', [
                'taskCompletionData'   => $taskCompletionData,
                'performanceChartData' => $performanceChartData,
                'quizAnalyticsData'    => $quizAnalyticsData,
                'upcomingAssignments'  => $upcomingAssignments,
                'latestAnnouncements'  => $latestAnnouncements,
            ]);
        }
    })->name('dashboard');

    // ===== 2. THÔNG BÁO (NOTIFICATIONS) =====
    Route::get('/notifications', function (Request $request) {
        return $request->user()->notifications()->take(20)->get();
    })->name('notifications.index');

    Route::post('/notifications/{id}/read', function (Request $request, $id) {
        $notification = $request->user()->notifications()->find($id);
        if ($notification) {
            $notification->markAsRead();
        }
        return response()->noContent();
    });
    
    Route::post('/notifications/read-all', function (Request $request) {
        $request->user()->unreadNotifications->markAsRead();
        return response()->noContent();
    })->name('notifications.readAll');

    // ===== 3. QUẢN LÝ LỚP HỌC & CHỦ ĐỀ =====
    Route::post('/classrooms/join', [StudentClassroomController::class, 'join'])->name('classrooms.join');

    Route::get('/teams/{team}/feed', function (Team $team) {
        if (Gate::denies('view', $team)) {
            abort(403);
        }
        $team->load(['owner', 'users', 'topics.user']); 
        return Inertia::render('Teams/Feed', [
            'team' => $team,
            'topics' => $team->topics->sortByDesc('created_at')->values(), 
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
    Route::get('/attendance/{session}/{token}', [AttendanceController::class, 'joinByQr'])->name('attendance.join');
    Route::post('/attendance/join-code', [AttendanceController::class, 'joinByCode'])->name('attendance.join-code');
    Route::post('/attendance/toggle', [AttendanceController::class, 'toggle'])->name('attendance.toggle');
    Route::get('/teams/{team}/attendance-history', [AttendanceController::class, 'history'])->name('attendance.history');

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
    Route::post('/questions/generate-ai', [QuestionController::class, 'generateAiQuestions'])->name('questions.generate-ai');
    Route::post('/questions/store-bulk', [QuestionController::class, 'storeBulk'])->name('questions.store-bulk');

    // ===== 6. QUIZ (BÀI KIỂM TRA) =====
    Route::get('/posts/{post}/quiz/manage', [PostQuizController::class, 'manage'])->name('post.quiz.manage');
    Route::post('/posts/{post}/quiz/attach', [PostQuizController::class, 'attach'])->name('post.quiz.attach');
    Route::delete('/posts/{post}/quiz/detach', [PostQuizController::class, 'detach'])->name('post.quiz.detach');
    Route::post('/posts/{post}/quiz/generate', [PostQuizController::class, 'generate'])->name('post.quiz.generate');
    Route::post('/posts/{post}/quiz/save-manual', [PostQuizController::class, 'saveManualSettings'])->name('post.quiz.saveManual');
    Route::resource('quiz-templates', QuizTemplateController::class)->only(['store', 'destroy']);

    Route::post('/posts/{post}/quiz/start', [QuizAttemptController::class, 'start'])->name('quiz.start');
    Route::get('/attempt/{attempt}/question/{questionNumber}', [QuizAttemptController::class, 'showQuestion'])->name('quiz.question.show');
    Route::post('/attempt/{attempt}/question/{questionNumber}', [QuizAttemptController::class, 'saveAnswer'])->name('quiz.question.save');
    Route::get('/attempt/{attempt}/submit', [QuizAttemptController::class, 'submitPage'])->name('quiz.submitPage');
    Route::post('/attempt/{attempt}/submit', [QuizAttemptController::class, 'finishAttempt'])->name('quiz.finish');
    Route::get('/attempt/{attempt}/results', [QuizAttemptController::class, 'showResults'])->name('quiz.results');
    Route::post('/quiz-attempts/{attempt}/log-violation', [QuizAttemptController::class, 'logViolation'])->name('quiz.log-violation');
});

// ===== 7. NỘP BÀI & CHẤM ĐIỂM (SUBMISSIONS) =====
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/submissions/{submission}/ai-grade', [SubmissionController::class, 'requestAiGrading'])->name('submissions.ai-grade');
    Route::post('/posts/{post}/submit', [SubmissionController::class, 'store'])->name('submissions.store');
    Route::get('/analytics/class/{team}', [AnalyticsController::class, 'show'])->name('analytics.class.show');
    Route::get('/posts/{post}/submissions', [SubmissionController::class, 'index'])->name('submissions.index');
    Route::put('/submissions/{submission}/grade', [SubmissionController::class, 'grade'])->name('submissions.grade');
    Route::get('/submissions/file/{submission_file}', [SubmissionController::class, 'downloadFile'])->name('submissions.downloadFile');
});