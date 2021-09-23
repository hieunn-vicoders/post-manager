<?php

namespace VCComponent\Laravel\Post\Contracts;

use Closure;

interface Pipe
{
    public function handle($content, Closure $next);
}
