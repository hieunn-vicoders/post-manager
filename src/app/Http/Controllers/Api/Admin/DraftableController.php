<?php

namespace VCComponent\Laravel\Post\Http\Controllers\Api\Admin;

use VCComponent\Laravel\Post\Traits\DraftableAdminMethods;
use VCComponent\Laravel\Vicoders\Core\Controllers\ApiController;

class DraftableController extends ApiController
{
    use DraftableAdminMethods;

    protected $repository;
    protected $entity;
    protected $validator;
}
