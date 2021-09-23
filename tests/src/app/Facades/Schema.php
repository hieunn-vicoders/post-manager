<?php

namespace VCComponent\Laravel\Post\Facades;

use Illuminate\Support\Facades\Facade;

class Schema extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'vcc.post.schema';
    }
}
