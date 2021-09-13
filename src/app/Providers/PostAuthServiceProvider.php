<?php

namespace VCComponent\Laravel\Post\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class PostAuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        
        Gate::define('view-post', 'VCComponent\Laravel\Post\Contracts\PostPolicyInterface@ableToShow');
        Gate::define('create-post', 'VCComponent\Laravel\Post\Contracts\PostPolicyInterface@ableToCreate');
        Gate::define('update-post', 'VCComponent\Laravel\Post\Contracts\PostPolicyInterface@ableToUpdate');
        Gate::define('update-item-post', 'VCComponent\Laravel\Post\Contracts\PostPolicyInterface@ableToUpdateItem');
        Gate::define('delete-post', 'VCComponent\Laravel\Post\Contracts\PostPolicyInterface@ableToDelete');
        //
    }
}
