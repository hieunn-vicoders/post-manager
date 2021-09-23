<?php

namespace VCComponent\Laravel\Post\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use VCComponent\Laravel\Post\Entities\Post;

trait HasPostTrait
{
    public function posts()
    {
        return $this->morphedByMany(Post::class, 'categoryable');
    }
}
