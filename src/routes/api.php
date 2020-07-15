<?php

if (config('post.models.post') !== null) {
    $model_class = config('post.models.post');
} else {
    $model_class = VCComponent\Laravel\Post\Entities\Post::class;
}

$model     = new $model_class;
$postTypes = $model->postTypes();

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) use ($postTypes) {
    $api->group(['prefix' => config('post.namespace')], function ($api) use ($postTypes) {
        $api->group(['prefix' => 'admin'], function ($api) use ($postTypes) {

            $api->delete('posts/{id}/force', 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface@forceDelete');
            $api->delete('posts/trash/all', 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface@deleteAllTrash');
            $api->delete('posts/trash/bulk', 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface@bulkDeleteTrash');
            $api->delete('posts/trash/{id}', 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface@deleteTrash');
            $api->get('posts/trash/all', 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface@getAllTrash');
            $api->get('posts/trash', 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface@trash');
            $api->put('posts/trash/bulk/restores', 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface@bulkRestore');
            $api->put('posts/trash/{id}/restore', 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface@restore');

            $api->put('posts/{id}/date', 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface@changeDate');

            $api->get('posts/list-all', 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface@allListPostAndType');

            $api->get('posts/all', 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface@list');
            $api->put('posts/status/bulk', 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface@bulkUpdateStatus');
            $api->put('posts/status/{id}', 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface@updateStatusItem');

            $api->delete('posts/bulk', 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface@bulkDelete');
            $api->resource('posts', 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface');
            $api->get('postTypes', 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface@getType');

            $api->get('pages/all', 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface@list');
            $api->put('pages/status/bulk', 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface@bulkUpdateStatus');
            $api->put('pages/status/{id}', 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface@updateStatusItem');
            $api->resource('pages', 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface');

            $api->resource('draft', 'VCComponent\Laravel\Post\Http\Controllers\Api\Admin\DraftableController');

            if (count($postTypes)) {
                foreach ($postTypes as $resource) {
                    $api->delete($resource . '/bulk', 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface@bulkDelete');
                    $api->delete($resource . '/{id}/force', 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface@forceDelete');
                    $api->delete($resource . '/trash/all', 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface@deleteAllTrash');
                    $api->delete($resource . '/trash/bulk', 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface@bulkDeleteTrash');
                    $api->delete($resource . '/trash/{id}', 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface@deleteTrash');
                    $api->get($resource . '/trash/all', 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface@getAllTrash');
                    $api->get($resource . '/trash', 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface@trash');
                    $api->put($resource . '/trash/bulk/restores', 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface@bulkRestore');
                    $api->put($resource . '/trash/{id}/restore', 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface@restore');

                    $api->get($resource . '/all', 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface@list');
                    $api->put($resource . '/status/bulk', 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface@bulkUpdateStatus');
                    $api->put($resource . '/status/{id}', 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface@updateStatusItem');
                    $api->resource($resource, 'VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface');
                }
            }
        });

        $api->get('posts/all', 'VCComponent\Laravel\Post\Contracts\PostControllerInterface@list');
        $api->put('posts/status/bulk', 'VCComponent\Laravel\Post\Contracts\PostControllerInterface@bulkUpdateStatus');
        $api->put('posts/status/{id}', 'VCComponent\Laravel\Post\Contracts\PostControllerInterface@updateStatusItem');
        $api->resource('posts', 'VCComponent\Laravel\Post\Contracts\PostControllerInterface');

        $api->get('pages/all', 'VCComponent\Laravel\Post\Contracts\PostControllerInterface@list');
        $api->put('pages/status/bulk', 'VCComponent\Laravel\Post\Contracts\PostControllerInterface@bulkUpdateStatus');
        $api->put('pages/status/{id}', 'VCComponent\Laravel\Post\Contracts\PostControllerInterface@updateStatusItem');
        $api->resource('pages', 'VCComponent\Laravel\Post\Contracts\PostControllerInterface');

        if (count($postTypes)) {
            foreach ($postTypes as $resource) {
                $api->get($resource . '/all', 'VCComponent\Laravel\Post\Contracts\PostControllerInterface@list');
                $api->put($resource . '/status/bulk', 'VCComponent\Laravel\Post\Contracts\PostControllerInterface@bulkUpdateStatus');
                $api->put($resource . '/status/{id}', 'VCComponent\Laravel\Post\Contracts\PostControllerInterface@updateStatusItem');
                $api->resource($resource, 'VCComponent\Laravel\Post\Contracts\PostControllerInterface');
            }
        }
    });
});
