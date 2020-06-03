<?php

namespace VCComponent\Laravel\Post\Contracts;

use Illuminate\Http\Request;

interface ViewPostListControllerInterface
{
    public function index(Request $request);
}
