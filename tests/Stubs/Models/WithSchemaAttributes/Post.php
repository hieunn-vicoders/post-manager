<?php

namespace VCComponent\Laravel\Post\Test\Stubs\Models\WithSchemaAttributes;

use VCComponent\Laravel\Post\Entities\Post as BasePost;

class Post extends BasePost
{
    protected $table = 'posts';

    public function schema()
    {
        return [
            'author'  => [
                'type' => 'string',
                'rule' => ['required'],
            ],
            'company' => [
                'type' => 'string',
                'rule' => [],
            ],
        ];
    }
}
