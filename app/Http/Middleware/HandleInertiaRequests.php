<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Laravel\Jetstream\Jetstream;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Lấy tất cả dữ liệu shared mặc định từ (parent) và Jetstream
        $shared = parent::share($request);

        // ===== BẮT ĐẦU SỬA LỖI & THÊM GAMIFICATION =====
        
        // 1. Thêm dữ liệu tùy chỉnh vào 'auth.user' một cách an toàn
        // Chúng ta chỉ thêm nếu 'auth.user' đã được định nghĩa (tức là user đã đăng nhập)
        if (isset($shared['auth']) && $shared['auth']['user']) {
            $user = $request->user();

            // -- Thêm Role --
            $shared['auth']['user']['role'] = $user->role;
            
            // -- [MỚI] THÊM GAMIFICATION DATA --
            // Các dữ liệu này cần thiết để hiển thị thanh Level trên Vue.js
            $shared['auth']['user']['xp'] = $user->xp;
            $shared['auth']['user']['level'] = $user->level;
            $shared['auth']['user']['xp_progress'] = $user->xp_progress; // Từ Accessor trong Model User
            $shared['auth']['user']['next_level_xp'] = $user->next_level_xp; // Từ Accessor trong Model User
            // ----------------------------------

            // Đảm bảo 'all_teams' được tải (nếu Jetstream chưa tải)
            if (Jetstream::hasTeamFeatures()) {
                 $shared['auth']['user']['all_teams'] = $user->allTeams();
            }
        }

        // 2. Thêm Flash Messages (cho thông báo "Tham gia thành công")
        $shared['flash'] = [
            'success' => $request->session()->get('success'),
            'status' => $request->session()->get('status'),
            'error' => $request->session()->get('error'),
        ];
        
        // 3. Thêm config Jetstream (để 'canCreateTeams' hoạt động)
        $shared['jetstream'] = [
            'canCreateTeams' => $request->user() && // <-- Phải kiểm tra user tồn tại
                                Jetstream::hasTeamFeatures() &&
                                $request->user()->can('create', Jetstream::newTeamModel()),
            'hasTeamFeatures' => Jetstream::hasTeamFeatures(),
            'managesProfilePhotos' => Jetstream::managesProfilePhotos(),
            'hasApiFeatures' => Jetstream::hasApiFeatures(),
        ];

        // ===== KẾT THÚC =====

        return $shared;
    }
}