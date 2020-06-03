<?php

namespace VCComponent\Laravel\Post\Contracts;

use Illuminate\Http\Request;

interface ViewDrafDetailControllerInterface
{
    public function show($id, Request $request);
}
