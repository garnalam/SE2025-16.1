<?php

namespace App\Http\Controllers\Teacher; // Đảm bảo namespace chính xác

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Team;
use App\Models\Post;
use App\Models\Submission;
use App\Models\Comment;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Hiển thị dashboard cho giáo viên.
     */
    public function index()
    {
        $teacher = Auth::user();
        $teacherId = $teacher->id;
        $teacherTeamIds = $teacher->ownedTeams->pluck('id');

        // --- 1. THỐNG KÊ TỔNG QUAN ---
        $totalClasses = $teacherTeamIds->count();
        $studentIds = DB::table('team_user')
                        ->whereIn('team_id', $teacherTeamIds)
                        ->distinct('user_id')
                        ->pluck('user_id');
        $totalStudents = User::whereIn('id', $studentIds)
                             ->where('role', 'student')
                             ->count();
        $activeAssignments = Post::whereIn('team_id', $teacherTeamIds)
                                 ->where('post_type', 'assignment')
                                 ->where('due_date', '>', now())
                                 ->count();
        $totalUngradedSubmissions = Submission::whereNull('grade')
                                    ->whereHas('post', function ($query) use ($teacherTeamIds) {
                                        $query->whereIn('team_id', $teacherTeamIds);
                                    })
                                    ->count();

        // --- 2. CẦN HÀNH ĐỘNG NGAY ---
        $assignmentsToGrade = Post::where('post_type', 'assignment')
            ->whereIn('team_id', $teacherTeamIds)
            ->whereHas('submissions', function ($query) { $query->whereNull('grade'); })
            ->withCount(['submissions as ungraded_submissions_count' => function ($query) {
                $query->whereNull('grade');
            }])
            ->with('team') 
            ->orderBy('due_date', 'asc') 
            ->take(5) 
            ->get();
            

        // --- 3. HOẠT ĐỘNG GẦN ĐÂY ---
        $recentSubmissions = Submission::with('user', 'post.team')
            ->whereHas('post', function ($query) use ($teacherTeamIds) { $query->whereIn('team_id', $teacherTeamIds); })
            ->whereHas('user', function ($query) { $query->where('role', 'student'); })
            ->latest() ->take(10)->get()
            ->map(fn($item) => $item->setAttribute('activity_type', 'submission')->setAttribute('timestamp', $item->created_at))
            ->toBase(); // <--- THÊM DÒNG NÀY
        $recentComments = Comment::with('user', 'post.team')
            ->whereHas('post', function ($query) use ($teacherTeamIds) { $query->whereIn('team_id', $teacherTeamIds); })
            ->whereHas('user', function ($query) { $query->where('role', 'student'); })
            ->latest()->take(10)->get()
            ->map(fn($item) => $item->setAttribute('activity_type', 'comment')->setAttribute('timestamp', $item->created_at))
            ->toBase(); // <--- THÊM DÒNG NÀY
        $recentPosts = Post::with('team')
            ->where('user_id', $teacherId) 
            ->latest()->take(10)->get()
            ->map(fn($item) => $item->setAttribute('activity_type', 'post')->setAttribute('timestamp', $item->created_at))
            ->toBase(); // <--- THÊM DÒNG NÀY
        $recentJoins = DB::table('team_user')
            ->join('users', 'team_user.user_id', '=', 'users.id')
            ->join('teams', 'team_user.team_id', '=', 'teams.id')
            ->whereIn('team_user.team_id', $teacherTeamIds)
            ->where('users.role', 'student')
            ->select('users.name as user_name', 'teams.name as team_name', 'team_user.created_at')
            ->orderByDesc('team_user.created_at')->take(10)->get()
            ->map(fn($item) => (object)[
                'activity_type' => 'join',
                'timestamp' => \Carbon\Carbon::parse($item->created_at),
                'user_name' => $item->user_name,
                'team_name' => $item->team_name
            ]);
        $activityFeed = $recentSubmissions->merge($recentComments)->merge($recentPosts)->merge($recentJoins)
            ->sortByDesc('timestamp')->take(10);

        // --- TRẢ DỮ LIỆU VỀ CHO INERTIA ---
        return inertia('Dashboard', [
            'stats' => [
                'totalClasses' => $totalClasses,
                'totalStudents' => $totalStudents,
                'activeAssignments' => $activeAssignments,
                'totalUngradedSubmissions' => $totalUngradedSubmissions,
            ],
            'priorityActions' => [
                'assignmentsToGrade' => $assignmentsToGrade,
            ],
            'activityFeed' => $activityFeed->values(),
            // V↓↓↓ THÊM DÒNG NÀY V↓↓↓
    'ownedTeams' => $teacher->ownedTeams->map(fn($team) => [
        'id' => $team->id,
        'name' => $team->name
    ]),
    // V↑↑↑ THÊM DÒNG NÀY V↑↑↑
        ]);
    }
}