<?php

return [

    'namespace'       => env('POST_COMPONENT_NAMESPACE', ''),

    'models'          => [
        'post' => VCComponent\Laravel\Post\Entities\Post::class,
        'draft' => VCComponent\Laravel\Post\Entities\Draftable::class,
    ],

    'transformers'    => [
        'post' => VCComponent\Laravel\Post\Transformers\PostTransformer::class,
        'draft' => VCComponent\Laravel\Post\Transformers\DraftableTransformer::class,
    ],

    'viewModels'      => [
        'postList'   => VCComponent\Laravel\Post\ViewModels\PostList\PostListViewModel::class,
        'postDetail' => VCComponent\Laravel\Post\ViewModels\PostDetail\PostDetailViewModel::class,
        'draft'       => VCComponent\Laravel\Post\ViewModels\DraftDetail\DraftDetailViewModel::class,
    ],

    'auth_middleware' => [
        'admin'    => [
            'middleware' => '',
            'except'     => [],
        ],
        'frontend' => [
            'middleware' => '',
            'except'     => [],
        ],
    ],
    'cache'           => [
        'enabled' => false,
        'minutes' => 5,
    ],
];
