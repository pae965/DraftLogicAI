<?php

namespace App\Providers;

use App\Models\Article;
use App\Policies\ArticlePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Model → Policy mappings
     */
    protected $policies = [
        Article::class => ArticlePolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
