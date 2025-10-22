<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\PostController;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Laravel\Jetstream\Jetstream;

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
    // Route dashboard mặc định (sẽ dành cho Teacher)
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // --- THÊM ROUTE MỚI CHO STUDENT ---
    Route::get('/student-dashboard', function () {
        return Inertia::render('StudentDashboard');
    })->name('student.dashboard');
    // --- HẾT PHẦN THÊM MỚI ---

    // Route để TẠO bài đăng
    Route::post('/teams/{team}/posts', [PostController::class, 'store'])->name('posts.store');

    // ROUTE MỚI CHO TRANG FEED (Đã cải tiến với Route-Model Binding)
    Route::get('/teams/{team}/feed', function (Team $team) { // <-- Thay đổi ở đây
        if (Gate::denies('view', $team)) {
            abort(403);
        }
        return Inertia::render('Teams/Feed', [
            'team' => $team,
            'posts' => $team->posts()->with('user')->latest()->get(),
            'permissions' => [
                'canAddTeamMembers' => Gate::check('addTeamMember', $team),
            ],
        ]);
    })->name('teams.feed');

    // ROUTE CÀI ĐẶT (Đã cải tiến và bổ sung đầy đủ permissions)
    Route::get('/teams/{team}', function (Team $team) { // <-- Thay đổi ở đây
        if (Gate::denies('view', $team)) {
            abort(403);
        }
        return Inertia::render('Teams/Show', [
            'team' => $team->load('owner', 'users', 'teamInvitations'),
            'availableRoles' => array_values(Jetstream::$roles),
            'availablePermissions' => Jetstream::$permissions,
            'defaultPermissions' => Jetstream::$defaultPermissions,
            // ✅ ĐÂY LÀ PHẦN QUAN TRỌNG BỊ THIẾU
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