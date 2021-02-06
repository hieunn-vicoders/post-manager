<?php

namespace VCComponent\Laravel\Post\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use VCComponent\Laravel\Post\Commands\SchemaCommand;
use VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface;
use VCComponent\Laravel\Post\Contracts\PostControllerInterface;
use VCComponent\Laravel\Post\Contracts\ViewDraftDetailControllerInterface;
use VCComponent\Laravel\Post\Contracts\ViewPostDetailControllerInterface;
use VCComponent\Laravel\Post\Contracts\ViewPostListControllerInterface;
use VCComponent\Laravel\Post\Entities\Post as BaseModel;
use VCComponent\Laravel\Post\Http\Controllers\Api\Admin\PostController as AdminPostController;
use VCComponent\Laravel\Post\Http\Controllers\Api\Frontend\PostController;
use VCComponent\Laravel\Post\Http\Controllers\Web\DraftDetailController as ViewDraftDetailController;
use VCComponent\Laravel\Post\Http\Controllers\Web\PostDetailController as ViewPostDetailController;
use VCComponent\Laravel\Post\Http\Controllers\Web\PostListController as ViewPostListController;
use VCComponent\Laravel\Post\Repositories\DraftableRepository;
use VCComponent\Laravel\Post\Repositories\DraftableRepositoryEloquent;
use VCComponent\Laravel\Post\Repositories\PostRepository;
use VCComponent\Laravel\Post\Repositories\PostRepositoryEloquent;
use VCComponent\Laravel\Post\Repositories\PostSchemaRepository;
use VCComponent\Laravel\Post\Repositories\PostSchemaRepositoryEloquent;
use VCComponent\Laravel\Post\Repositories\PostSchemaRuleRepository;
use VCComponent\Laravel\Post\Repositories\PostSchemaRuleRepositoryEloquent;
use VCComponent\Laravel\Post\Repositories\PostSchemaTypeRepository;
use VCComponent\Laravel\Post\Repositories\PostSchemaTypeRepositoryEloquent;
use VCComponent\Laravel\Post\Services\Post;
use VCComponent\Laravel\Post\Services\SchemaService;
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
        if (isset(config('post.models')['post'])) {
            $model       = config('post.models.post');
            $this->model = $model;
        } else {
            $this->model = BaseModel::class;
        }

        Relation::morphMap([
            'posts' => $this->model,
        ]);

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        $this->publishes([
            __DIR__ . '/../../config/post.php' => config_path('post.php'),
        ], 'config');

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
        $this->app->bind(PostSchemaRepository::class, PostSchemaRepositoryEloquent::class);
        $this->app->bind(PostSchemaTypeRepository::class, PostSchemaTypeRepositoryEloquent::class);
        $this->app->bind(PostSchemaRuleRepository::class, PostSchemaRuleRepositoryEloquent::class);

        $this->registerViewModels();
        $this->registerControllers();

        $this->app->bind('vcc.post.schema', SchemaService::class);
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
        $this->app->bind(ViewDraftDetailControllerInterface::class, ViewDraftDetailController::class);
    }
}
