<?php

namespace VCComponent\Laravel\Post\Contracts;

use Illuminate\Http\Request;

interface ViewPostDetailControllerInterface
{
    public function show($id, Request $request);
}
