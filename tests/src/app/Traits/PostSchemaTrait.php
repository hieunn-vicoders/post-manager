<?php

namespace VCComponent\Laravel\Post\Traits;

use VCComponent\Laravel\Post\Entities\PostMeta;

trait PostSchemaTrait
{
    public function postTypes()
    {
        return [];
    }

    public function postMetas()
    {
        return $this->hasMany(PostMeta::class);
    }

    public function schema()
    {
        return [];
    }

    public function pagesSchema()
    {
        return [];
    }
}
