<?php

namespace App\Actions\Jetstream;

use App\Models\Team;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Laravel\Jetstream\Contracts\AddsTeamMembers;
use Laravel\Jetstream\Events\AddingTeamMember;
use Laravel\Jetstream\Events\TeamMemberAdded;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\Rules\Role;

class AddTeamMember implements AddsTeamMembers
{
    /**
     * Add a new team member to the given team.
     */
    public function add(User $user, Team $team, string $email, ?string $role = null): void
    {
        Gate::forUser($user)->authorize('addTeamMember', $team);

        // 1. Tìm User trước để lấy thông tin Role của họ
        $newTeamMember = Jetstream::findUserByEmailOrFail($email);

        // 2. LOGIC TỰ ĐỘNG: Nếu role truyền vào là null, lấy role từ bảng users của người đó
        if (is_null($role)) {
            $role = $newTeamMember->role; // Sẽ lấy 'teacher' hoặc 'student'
        }

        // 3. Validate (Lúc này $role đã có giá trị, không còn bị null nữa)
        $this->validate($team, $email, $role);

        AddingTeamMember::dispatch($team, $newTeamMember);

        // 4. Attach vào team với role chuẩn
        $team->users()->attach(
            $newTeamMember, ['role' => $role]
        );

        TeamMemberAdded::dispatch($team, $newTeamMember);
    }

    /**
     * Validate the add member operation.
     */
    protected function validate($team, string $email, ?string $role): void
{
    Validator::make([
        'email' => $email,
        'role' => $role,
    ], $this->rules(), [
        'email.exists' => __('We were unable to find a registered user with this email address.'),
    ])->after(
        $this->ensureUserIsNotAlreadyOnTeam($team, $email)
    )->validateWithBag('addTeamMember');
}

    protected function rules(): array
    {
        return array_filter([
            'email' => ['required', 'email', 'exists:users'],
            'role' => Jetstream::hasRoles()
                            ? ['required', 'string', new Role]
                            : null,
        ]);
    }

    protected function ensureUserIsNotAlreadyOnTeam(Team $team, string $email): Closure
    {
        return function ($validator) use ($team, $email) {
            $validator->errors()->addIf(
                $team->hasUserWithEmail($email),
                'email',
                __('This user already belongs to the team.')
            );
        };
    }
}
