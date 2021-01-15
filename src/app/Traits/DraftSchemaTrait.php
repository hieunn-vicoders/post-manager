<?php

namespace VCComponent\Laravel\Post\Traits;

trait DraftSchemaTrait
{
    public function draftTypes()
    {
        return [
            'products',
            'posts'
        ];
    }

}
