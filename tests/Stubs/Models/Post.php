<?php

namespace VCComponent\Laravel\Post\Test\Stubs\Models;

use VCComponent\Laravel\Post\Entities\Post as BasePost;

class Post extends BasePost
{

    public function postTypes()
    {
        return [
            'about',
        ];
    }
}
