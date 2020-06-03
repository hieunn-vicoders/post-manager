<?php

namespace VCComponent\Laravel\Post\Http\Controllers\Api\Admin;

use VCComponent\Laravel\Post\Contracts\AdminPostControllerInterface;
use VCComponent\Laravel\Post\Traits\Helpers;
use VCComponent\Laravel\Post\Traits\PostAdminMethods;
use VCComponent\Laravel\Vicoders\Core\Controllers\ApiController;

class PostController extends ApiController implements AdminPostControllerInterface
{
    use PostAdminMethods, Helpers;

    protected $repository;
    protected $entity;
    protected $validator;
    protected $transformer;
    protected $constraint;
}
