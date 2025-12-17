<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\Submission;
use App\Models\Comment;
use App\Models\Post; 
use App\Models\User;
use Carbon\Carbon;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Jetstream;
use Jenssegers\Agent\Agent;

class UserProfileController extends Controller
{
    // --- HÀM TÍNH TOÁN HEATMAP (PHIÊN BẢN ĐỒNG BỘ TIMEZONE) ---
    private function getHeatmapData($userId)
    {
        // 1. Xác định thời gian theo múi giờ của APP (Quan trọng)
        $timezone = config('app.timezone'); 
        $endDate = Carbon::now()->setTimezone($timezone); // Lấy giờ hiện tại theo VN
$startDate = $endDate->copy()->subDays(52 * 7 - 1);
        // 2. Hàm Helper: Lấy dữ liệu và gom nhóm theo ngày đã chuẩn hóa
        $fetchAndCount = function($modelClass) use ($userId, $timezone) {
            return $modelClass::where('user_id', $userId)
                ->get(['created_at']) // Lấy tất cả, không lọc whereBetween để tránh sót
                ->groupBy(function ($item) use ($timezone) {
                    // Ép kiểu về Carbon và chuyển sang múi giờ VN
                    $date = $item->created_at instanceof Carbon 
                        ? $item->created_at 
                        : Carbon::parse($item->created_at);
                    
                    return $date->setTimezone($timezone)->format('Y-m-d');
                })
                ->map->count();
        };

        // 3. Lấy dữ liệu từ 3 nguồn
        $submissionCounts = $fetchAndCount(Submission::class);
        $commentCounts = $fetchAndCount(Comment::class);
        $postCounts = $fetchAndCount(Post::class);

        // 4. Tổng hợp dữ liệu vào biểu đồ
        $heatmapData = [];
        $totalContributions = 0;

        // Chạy vòng lặp theo đúng múi giờ đã set ở bước 1
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            
            // Cộng dồn
            $count = ($submissionCounts[$dateStr] ?? 0) 
                   + ($commentCounts[$dateStr] ?? 0) 
                   + ($postCounts[$dateStr] ?? 0);
            
            $totalContributions += $count;

            // Phân cấp màu sắc
            $level = match(true) {
                $count == 0 => 0,
                $count <= 2 => 1,
                $count <= 5 => 2,
                $count <= 9 => 3,
                default => 4
            };

            $heatmapData[] = ['date' => $dateStr, 'count' => $count, 'level' => $level];
        }

        return [
            'heatmap' => $heatmapData,
            'total' => $totalContributions
        ];
    }

    // --- HÀM PHỤ: LẤY BÀI TIÊU BIỂU ---
    private function getPinnedMissions($userId) {
        return Submission::where('user_id', $userId)
            ->whereNotNull('grade')
            ->orderByDesc('grade')
            ->take(6)
            ->with(['post.team'])
            ->get()
            ->map(function ($sub) {
                return [
                    'id' => $sub->id,
                    'title' => $sub->post->title,
                    'language' => 'PHP',
                    'grade' => $sub->grade,
                    'max_points' => $sub->post->max_points,
                    'class_name' => $sub->post->team->name ?? 'Lớp học',
                    'updated_at' => $sub->updated_at->diffForHumans(),
                ];
            });
    }

    // --- TRANG CÁ NHÂN CỦA MÌNH (Settings) ---
// --- TRANG CÁ NHÂN CỦA MÌNH (Settings) ---
    public function show(Request $request)
    {
        $user = Auth::user();
        
        $heatmapInfo = $this->getHeatmapData($user->id);
        $pinnedMissions = $this->getPinnedMissions($user->id);

        // [MỚI] Lấy danh sách chi tiết Followers
        $followersList = $user->followers->map(function($f) {
            return [
                'id' => $f->id,
                'name' => $f->name,
                'profile_photo_url' => $f->profile_photo_url,
                'email' => $f->email,
            ];
        });

        // [MỚI] Lấy danh sách chi tiết Following
        $followingList = $user->followings->map(function($f) {
            return [
                'id' => $f->id,
                'name' => $f->name,
                'profile_photo_url' => $f->profile_photo_url,
                'email' => $f->email,
            ];
        });

        $stats = [
            'total_contributions' => $heatmapInfo['total'],
            'followers_count' => $followersList->count(), // Đếm từ danh sách đã lấy
            'following_count' => $followingList->count(),
            'followers_list' => $followersList, // Truyền danh sách xuống Vue
            'following_list' => $followingList,
            'bio' => $user->bio ?? "Chưa cập nhật giới thiệu.", 
        ];

        return Inertia::render('Profile/Show', [
            'jetstream' => [
                'canUpdateProfileInformation' => Features::enabled(Features::updateProfileInformation()),
                'canUpdatePassword' => Features::enabled(Features::updatePasswords()),
                'canManageTwoFactorAuthentication' => Features::canManageTwoFactorAuthentication(),
                'hasAccountDeletionFeatures' => Jetstream::hasAccountDeletionFeatures(),
                'managesProfilePhotos' => Jetstream::managesProfilePhotos(),
            ],
            'confirmsTwoFactorAuthentication' => Features::enabled(Features::twoFactorAuthentication()),
            'sessions' => $this->sessions($request)->all(), 
            'heatmap' => $heatmapInfo['heatmap'],
            'pinnedMissions' => $pinnedMissions,
            'stats' => $stats,
            'organizations' => $user->allTeams(), 
        ]);
    }

    // --- TRANG CÁ NHÂN CÔNG KHAI (Public) ---
    public function publicProfile(Request $request, User $user)
    {
        $heatmapInfo = $this->getHeatmapData($user->id);
        $pinnedMissions = $this->getPinnedMissions($user->id);

        // [MỚI] Copy logic lấy danh sách tương tự hàm show
        $followersList = $user->followers->map(function($f) {
            return [
                'id' => $f->id,
                'name' => $f->name,
                'profile_photo_url' => $f->profile_photo_url,
                'email' => $f->email,
            ];
        });

        $followingList = $user->followings->map(function($f) {
            return [
                'id' => $f->id,
                'name' => $f->name,
                'profile_photo_url' => $f->profile_photo_url,
                'email' => $f->email,
            ];
        });

        $stats = [
            'total_contributions' => $heatmapInfo['total'],
            'followers_count' => $followersList->count(),
            'following_count' => $followingList->count(),
            'followers_list' => $followersList,
            'following_list' => $followingList,
            'bio' => $user->bio ?? "Chưa có giới thiệu.",
        ];

        $isFollowing = Auth::check() ? Auth::user()->isFollowing($user) : false;

        return Inertia::render('Profile/Public', [
            'profileUser' => $user,
            'isFollowing' => $isFollowing,
            'heatmap' => $heatmapInfo['heatmap'],
            'pinnedMissions' => $pinnedMissions,
            'stats' => $stats,
            'organizations' => $user->allTeams(),
            'badges' => $user->badges, 
        ]);
    }

    // --- CÁC HÀM CŨ ---
    protected function sessions(Request $request)
    {
        if (config('session.driver') !== 'database') {
            return collect();
        }
        return collect(
            DB::connection(config('session.connection'))->table(config('session.table', 'sessions'))
                ->where('user_id', $request->user()->getAuthIdentifier())
                ->orderBy('last_activity', 'desc')
                ->get()
        )->map(function ($session) use ($request) {
            $agent = $this->createAgent($session);
            return (object) [
                'agent' => [
                    'is_desktop' => $agent->isDesktop(),
                    'platform' => $agent->platform(),
                    'browser' => $agent->browser(),
                ],
                'ip_address' => $session->ip_address,
                'is_current_device' => $session->id === $request->session()->getId(),
                'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
            ];
        });
    }

    protected function createAgent($session)
    {
        return tap(new \Laravel\Jetstream\Agent, function ($agent) use ($session) {
            $agent->setUserAgent($session->user_agent);
        });
    }
}