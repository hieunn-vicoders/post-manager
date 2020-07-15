<?php

namespace VCComponent\Laravel\Post\Providers;

use Illuminate\Support\ServiceProvider;
use VCComponent\Laravel\Post\Commands\SchemaCommand;
use VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface;
use VCComponent\Laravel\Post\Contracts\PostControllerInterface;
use VCComponent\Laravel\Post\Contracts\ViewDrafDetailControllerInterface;
use VCComponent\Laravel\Post\Contracts\ViewPostDetailControllerInterface;
use VCComponent\Laravel\Post\Contracts\ViewPostListControllerInterface;
use VCComponent\Laravel\Post\Http\Controllers\Api\Admin\PostController as AdminPostController;
use VCComponent\Laravel\Post\Http\Controllers\Api\Frontend\PostController;
use VCComponent\Laravel\Post\Http\Controllers\Web\DrafDetailController as ViewDrafDetailController;
use VCComponent\Laravel\Post\Http\Controllers\Web\PostDetailController as ViewPostDetailController;
use VCComponent\Laravel\Post\Http\Controllers\Web\PostListController as ViewPostListController;
use VCComponent\Laravel\Post\Repositories\DraftableRepository;
use VCComponent\Laravel\Post\Repositories\DraftableRepositoryEloquent;
use VCComponent\Laravel\Post\Repositories\PostRepository;
use VCComponent\Laravel\Post\Repositories\PostRepositoryEloquent;
use VCComponent\Laravel\Post\Services\Post;
use VCComponent\Laravel\Post\Validators\PostValidator;
use VCComponent\Laravel\Post\Validators\PostValidatorInterface;

class PostComponentProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        $this->publishes([
            __DIR__ . '/../../config/post.php' => config_path('post.php'),
        ]);

        $this->loadViewsFrom(__DIR__ . '/../../resources/views/', 'post-manager');

        if ($this->app->runningInConsole()) {
            $this->commands([
                SchemaCommand::class,
            ]);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind("post", Post::class);
        $this->app->bind(PostRepository::class, PostRepositoryEloquent::class);
        $this->app->bind(PostValidatorInterface::class, PostValidator::class);
        $this->app->bind(DraftableRepository::class, DraftableRepositoryEloquent::class);

        $this->registerViewModels();
        $this->registerControllers();
    }

    private function registerViewModels()
    {
        $this->app->bind(PostListViewModelInterface::class, PostListViewModel::class);
        $this->app->bind(PostDetailViewModelInterface::class, PostDetailViewModel::class);
    }

    private function registerControllers()
    {
        $this->app->bind(ViewPostListControllerInterface::class, ViewPostListController::class);
        $this->app->bind(ViewPostDetailControllerInterface::class, ViewPostDetailController::class);
        $this->app->bind(AdminPostControllerInterface::class, AdminPostController::class);
        $this->app->bind(PostControllerInterface::class, PostController::class);
        $this->app->bind(ViewDrafDetailControllerInterface::class, ViewDrafDetailController::class);
    }
}
