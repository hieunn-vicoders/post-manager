<?php
if (config('post.models.post') !== null) {
    $model_class = config('post.models.post');
} else {
    $model_class = VCComponent\Laravel\Post\Entities\Post::class;
}

$model     = new $model_class;
$postTypes = array_keys($model->postTypes());
Route::prefix(config('post.namespace'))
    ->middleware('web')
    ->group(function () use ($postTypes) {

        Route::get('/posts', 'VCComponent\Laravel\Post\Contracts\ViewPostListControllerInterface@index');
        Route::get('/posts/{slug}', 'VCComponent\Laravel\Post\Contracts\ViewPostDetailControllerInterface@show');
        Route::get('/pages', 'VCComponent\Laravel\Post\Contracts\ViewPostListControllerInterface@index');
        Route::get('/pages/{slug}', 'VCComponent\Laravel\Post\Contracts\ViewPostDetailControllerInterface@show');
        if (count($postTypes)) {
            foreach ($postTypes as $type) {
                Route::get('/' . $type, 'VCComponent\Laravel\Post\Contracts\ViewPostListControllerInterface@index');
                Route::get('/' . $type . '/{slug}', 'VCComponent\Laravel\Post\Contracts\ViewPostDetailControllerInterface@show');
            }
        }
    });

if (config('post.models.draf') !== null) {
    $model_darfts = config('post.models.draf');
} else {
    $model_darfts = VCComponent\Laravel\Post\Entities\Draftable::class;
}

$model_darf = new $model_darfts;
$drafTypes  = $model_darf->drafTypes();

Route::prefix(config('post.namespace'))
    ->middleware('web')
    ->group(function () use ($drafTypes) {

        Route::get('/post-preview/{id}', 'VCComponent\Laravel\Post\Contracts\ViewDrafDetailControllerInterface@show');

        if (count($drafTypes)) {
            foreach ($drafTypes as $type) {
                Route::get('/preview/{id}/' . $type, 'VCComponent\Laravel\Post\Contracts\ViewDrafDetailControllerInterface@show');
            }
        }
    });
