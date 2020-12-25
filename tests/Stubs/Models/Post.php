<?php

namespace VCComponent\Laravel\Post\Test\Stubs\Models;

use VCComponent\Laravel\Post\Entities\Post as BasePost;

class Post extends BasePost
{
    public function schema()
    {
        return [
            'address' => [
                'type' => 'string',
                'rule' => [],
            ],
        ];
    }

    public function postTypes()
    {
        return [
            'about',
        ];
    }

    public function aboutSchema()
    {
        return [
            'about' => [
                'type' => 'string',
                'rule' => [],
            ],
        ];
    }

    public function pagesSchema()
    {
        return [
            'pages' => [
                'type' => 'string',
                'rule' => [],
            ],
        ];
    }
}
