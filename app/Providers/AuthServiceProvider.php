<?php

namespace App\Providers;

use App\Models\Team;
use App\Models\Topic; // <-- Thêm dòng này
use App\Policies\TeamPolicy;
use App\Policies\TopicPolicy; // <-- Thêm dòng này
use App\Policies\PostPolicy; // <-- THÊM DÒNG NÀY
use App\Models\Question; // Thêm dòng này
use App\Policies\QuestionPolicy; // Thêm dòng này
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\QuizAttempt; // <-- THÊM DÒNG NÀY
use App\Policies\QuizAttemptPolicy; // <-- THÊM DÒNG NÀY
use App\Models\Subject;
use App\Policies\SubjectPolicy;
use App\Models\Tag;
use App\Policies\TagPolicy;
use App\Models\QuizTemplate;
use App\Policies\QuizTemplatePolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Team::class => TeamPolicy::class,
        Topic::class => TopicPolicy::class, // <-- Thêm dòng này
        Post::class => PostPolicy::class, // <-- THÊM DÒNG NÀY
        Question::class => QuestionPolicy::class, // Thêm dòng này
        QuizAttempt::class => QuizAttemptPolicy::class, // <-- THÊM DÒNG NÀY
        Subject::class => SubjectPolicy::class, // <-- THÊM DÒNG NÀY
        Tag::class => TagPolicy::class,         // <-- THÊM DÒNG NÀY
        QuizTemplate::class => QuizTemplatePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}