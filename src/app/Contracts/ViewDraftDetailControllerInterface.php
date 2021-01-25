<?php

namespace VCComponent\Laravel\Post\Contracts;

use Illuminate\Http\Request;

interface ViewDraftDetailControllerInterface
{
    public function show($id, Request $request);
}
