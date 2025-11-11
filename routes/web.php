<?php
use App\Http\Controllers\PostController;
use App\Http\Controllers\QuestionController;
use App\Models\Submission;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostQuizController;
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
use App\Http\Controllers\QuizAttemptController;
use Illuminate\Support\Facades\Auth; // <-- Đã di chuyển lên đây
use Carbon\Carbon; // <-- Đã di chuyển lên đây
use App\Http\Controllers\SubjectController; // Thêm
use App\Http\Controllers\TagController;     // Thêm
use App\Http\Controllers\QuestionImportController;
use App\Http\Controllers\QuizTemplateController;
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
    
    Route::resource('subjects', SubjectController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('tags', TagController::class)->only(['index', 'store', 'update', 'destroy']);

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

    Route::get('/questions/import', [QuestionImportController::class, 'create'])->name('questions.import.create');
    Route::post('/questions/import', [QuestionImportController::class, 'store'])->name('questions.import.store');
    Route::get('/questions/import/template', [QuestionImportController::class, 'downloadTemplate'])->name('questions.import.template');


    // ===== ROUTES NGÂN HÀNG CÂU HỎI (Questions) - THÊM MỚI =====
    Route::resource('questions', QuestionController::class);
    // =========================================================

    // ===== ROUTES QUẢN LÝ CÂU HỎI TRONG QUIZ (Post) - THÊM MỚI =====
    // 1. Trang để quản lý (thêm/bớt) câu hỏi cho một bài quiz
    Route::get('/posts/{post}/quiz/manage', [PostQuizController::class, 'manage'])
         ->name('post.quiz.manage');
         
    // 2. Route để GẮN (attach) một câu hỏi từ ngân hàng vào bài quiz
    Route::post('/posts/{post}/quiz/attach', [PostQuizController::class, 'attach'])
         ->name('post.quiz.attach');
         
    // 3. Route để GỠ (detach) một câu hỏi khỏi bài quiz
    Route::delete('/posts/{post}/quiz/detach', [PostQuizController::class, 'detach'])
         ->name('post.quiz.detach');
    // =================================================================
    // 1. Route để xử lý logic "TẠO ĐỀ TỰ ĐỘNG"
    Route::post('/posts/{post}/quiz/generate', [PostQuizController::class, 'generate'])
         ->name('post.quiz.generate');
    // ================================================================
    Route::post('/posts/{post}/quiz/save-manual', [PostQuizController::class, 'saveManualSettings'])->name('post.quiz.saveManual');
    

    // 2. Routes để LƯU/XÓA "CẤU HÌNH MẪU" (Ý tưởng 1 của bạn)
    Route::resource('quiz-templates', QuizTemplateController::class)
         ->only(['store', 'destroy']);

    // 1. Nút "Bắt đầu làm bài"
    Route::post('/posts/{post}/quiz/start', [QuizAttemptController::class, 'start'])
         ->name('quiz.start');

    // 2. Hiển thị một câu hỏi (vd: .../question/1, .../question/2)
    Route::get('/attempt/{attempt}/question/{questionNumber}', [QuizAttemptController::class, 'showQuestion'])
         ->name('quiz.question.show');

    // 3. Lưu câu trả lời cho câu hỏi đó
    Route::post('/attempt/{attempt}/question/{questionNumber}', [QuizAttemptController::class, 'saveAnswer'])
         ->name('quiz.question.save');

    // 4. Hiển thị trang xác nhận nộp bài
    Route::get('/attempt/{attempt}/submit', [QuizAttemptController::class, 'submitPage'])
         ->name('quiz.submitPage');

    // 5. Nộp bài và chấm điểm
    Route::post('/attempt/{attempt}/submit', [QuizAttemptController::class, 'finishAttempt'])
         ->name('quiz.finish');
         
    // 6. Trang xem kết quả
    Route::get('/attempt/{attempt}/results', [QuizAttemptController::class, 'showResults'])
         ->name('quiz.results');
    // =======================================================

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


