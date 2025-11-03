<?php

namespace App\Providers;

use App\Models\Team;
use App\Models\Topic; // <-- Thêm dòng này
use App\Policies\TeamPolicy;
use App\Policies\TopicPolicy; // <-- Thêm dòng này
use App\Policies\PostPolicy; // <-- THÊM DÒNG NÀY
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

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