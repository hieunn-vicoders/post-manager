<?php

namespace VCComponent\Laravel\Post\Test\Stubs\Validators;

use VCComponent\Laravel\Post\Validators\PostValidator as BasePostValidator;
use VCComponent\Laravel\Post\Validators\PostValidatorInterface;
use VCComponent\Laravel\Vicoders\Core\Validators\ValidatorInterface;

class PostValidator extends BasePostValidator implements PostValidatorInterface
{
    protected $rules = [
        ValidatorInterface::RULE_ADMIN_CREATE  => [
            'title'       => ['required'],
            'description' => [],
            'content'     => [],
        ],
        ValidatorInterface::RULE_ADMIN_UPDATE  => [
            'title'       => ['required'],
            'description' => [],
            'content'     => [],
        ],
        ValidatorInterface::RULE_CREATE        => [
            'title'       => ['required'],
            'description' => [],
            'content'     => [],
        ],
        ValidatorInterface::RULE_UPDATE        => [
            'title'       => ['required'],
            'description' => [],
            'content'     => [],
        ],
        ValidatorInterface::BULK_UPDATE_STATUS => [
            'item_ids' => ['required'],
            'status'   => ['required'],
        ],
        ValidatorInterface::UPDATE_STATUS_ITEM => [
            'status' => ['required'],
        ],
    ];
}
