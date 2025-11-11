<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Laravel\Jetstream\Jetstream; // <-- Thêm Jetstream

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

        // ===== BẮT ĐẦU SỬA LỖI (DÒNG 40) =====
        
        // 1. Thêm 'role' vào 'auth.user' một cách an toàn
        // SỬA LỖI: Thêm "isset($shared['auth']) &&"
        // Chúng ta chỉ thêm 'role' nếu 'auth.user' đã được định nghĩa (tức là user đã đăng nhập)
        if (isset($shared['auth']) && $shared['auth']['user']) {
            $shared['auth']['user']['role'] = $request->user()->role;
            
            // Đảm bảo 'all_teams' được tải (nếu Jetstream chưa tải)
            // (Dòng này để chắc chắn, dù Jetstream thường sẽ tự làm)
            if (Jetstream::hasTeamFeatures() && $request->user()) {
                 $shared['auth']['user']['all_teams'] = $request->user()->allTeams();
            }
        }

        // 2. Thêm Flash Messages (cho thông báo "Tham gia thành công")
        $shared['flash'] = [
            'success' => $request->session()->get('success'), // <-- THÊM DÒNG NÀY
            'status' => $request->session()->get('status'),
            'error' => $request->session()->get('error'),
        ];
        
        // 3. Thêm config Jetstream (để 'canCreateTeams' hoạt động)
        // SỬA LỖI: Thêm $request->user() &&
        $shared['jetstream'] = [
            'canCreateTeams' => $request->user() && // <-- Phải kiểm tra user tồn tại
                                Jetstream::hasTeamFeatures() &&
                                $request->user()->can('create', Jetstream::newTeamModel()),
            'hasTeamFeatures' => Jetstream::hasTeamFeatures(),
            'managesProfilePhotos' => Jetstream::managesProfilePhotos(),
            'hasApiFeatures' => Jetstream::hasApiFeatures(),
        ];

        // ===== KẾT THÚC SỬA LỖI =====

        return $shared;
    }
}

