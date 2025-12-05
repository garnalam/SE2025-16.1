<?php

namespace App\Providers;

use App\Actions\Jetstream\AddTeamMember;
use App\Actions\Jetstream\CreateTeam;
use App\Actions\Jetstream\DeleteTeam;
use App\Actions\Jetstream\DeleteUser;
use App\Actions\Jetstream\InviteTeamMember;
use App\Actions\Jetstream\RemoveTeamMember;
use App\Actions\Jetstream\UpdateTeamName;
use Illuminate\Support\ServiceProvider;
use Laravel\Jetstream\Jetstream;

// ✅ THÊM CÁC DÒNG NÀY VÀO
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class JetstreamServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurePermissions();

        Jetstream::createTeamsUsing(CreateTeam::class);
        Jetstream::updateTeamNamesUsing(UpdateTeamName::class);
        Jetstream::addTeamMembersUsing(AddTeamMember::class);
        Jetstream::inviteTeamMembersUsing(InviteTeamMember::class);
        Jetstream::removeTeamMembersUsing(RemoveTeamMember::class);
        Jetstream::deleteTeamsUsing(DeleteTeam::class);
        Jetstream::deleteUsersUsing(DeleteUser::class);
        
        // ✅ THÊM TOÀN BỘ KHỐI CODE NÀY VÀO
        // Đây là phần định nghĩa quyền hạn quan trọng bị thiếu
        Gate::define('addTeamMember', function (User $user, Team $team) {
            return $user->ownsTeam($team);
        });

        Gate::define('updateTeamMember', function (User $user, Team $team, User $teamMember) {
            return $user->ownsTeam($team);
        });

        Gate::define('removeTeamMember', function (User $user, Team $team, User $teamMember) {
            return $user->ownsTeam($team);
        });
    }

    /**
     * Configure the roles and permissions that are available within the application.
     */
protected function configurePermissions(): void
{
    Jetstream::defaultApiTokenPermissions(['read']);

    // SỬA: Đổi 'admin' thành 'teacher' để khớp với bảng users
    Jetstream::role('teacher', 'Giáo viên', [
        'create',
        'read',
        'update',
        'delete',
        // Thêm các quyền khác nếu cần
    ])->description('Giáo viên quản lý lớp học.');

    // SỬA: Đổi 'editor' thành 'student' để khớp với bảng users
    Jetstream::role('student', 'Học sinh', [
        'read',
        // 'create', // Bật cái này nếu muốn học sinh đăng bài
    ])->description('Học sinh tham gia lớp học.');
}
}