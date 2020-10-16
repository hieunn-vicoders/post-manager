<?php

return [

    'namespace'       => env('POST_COMPONENT_NAMESPACE', ''),

    'models'          => [
        'post'         => App\Entities\Post::class,
        'postTypeMeta' => VCComponent\Laravel\Post\Entities\PostTypeMeta::class,
        'draf'         => VCComponent\Laravel\Post\Entities\Draftable::class,
    ],

    'transformers'    => [
        'post'         => VCComponent\Laravel\Post\Transformers\PostTransformer::class,
        'postTypeMeta' => VCComponent\Laravel\Post\Transformers\PostTypeMetaTransformer::class,
        'draf'         => VCComponent\Laravel\Post\Transformers\DraftableTransformer::class,
    ],

    'viewModels'      => [
        'postList'   => VCComponent\Laravel\Post\ViewModels\PostList\PostListViewModel::class,
        'postDetail' => VCComponent\Laravel\Post\ViewModels\PostDetail\PostDetailViewModel::class,
        'draf'       => VCComponent\Laravel\Post\ViewModels\DrafDetail\DrafDetailViewModel::class,
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
