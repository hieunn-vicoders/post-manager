<?php

namespace VCComponent\Laravel\Post\Http\Controllers\Api\Frontend;

use VCComponent\Laravel\Post\Contracts\PostControllerInterface;
use VCComponent\Laravel\Post\Traits\Helpers;
use VCComponent\Laravel\Post\Traits\PostFrontendMethods;
use VCComponent\Laravel\Vicoders\Core\Controllers\ApiController;

class PostController extends ApiController implements PostControllerInterface
{
    use PostFrontendMethods, Helpers;

    protected $repository;
    protected $entity;
    protected $validator;
    protected $transformer;
}
