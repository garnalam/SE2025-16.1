<?php

namespace App\Policies;

use App\Models\QuizTemplate;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class QuizTemplatePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, QuizTemplate $quizTemplate): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, QuizTemplate $quizTemplate): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, QuizTemplate $quizTemplate): bool
    {
        return $user->id === $template->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, QuizTemplate $quizTemplate): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, QuizTemplate $quizTemplate): bool
    {
        return false;
    }
}
