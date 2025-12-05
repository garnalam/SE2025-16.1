<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeamPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Team $team): bool
    {
        return $user->belongsToTeam($team);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Team $team): bool
    {
        // Cho phép nếu là Chủ phòng HOẶC có role là 'teacher' trong lớp này
        return $user->ownsTeam($team) || $user->hasTeamRole($team, 'teacher');
    }

    /**
     * Determine whether the user can add team members.
     */
   public function addTeamMember(User $user, Team $team): bool
{
    return $user->ownsTeam($team) || $user->hasTeamRole($team, 'teacher');
}

public function updateTeamMember(User $user, Team $team): bool
{
    return $user->ownsTeam($team) || $user->hasTeamRole($team, 'teacher');
}

public function removeTeamMember(User $user, Team $team): bool
{
    return $user->ownsTeam($team) || $user->hasTeamRole($team, 'teacher');
}

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Team $team): bool
    {
        return $user->ownsTeam($team);
    }
}
