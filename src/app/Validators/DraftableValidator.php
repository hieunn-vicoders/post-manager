<?php

namespace VCComponent\Laravel\Post\Validators;

use VCComponent\Laravel\Vicoders\Core\Validators\ValidatorInterface;
use VCComponent\Laravel\Vicoders\Core\Validators\AbstractValidator;

class DraftableValidator extends AbstractValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'payload' => 'required',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'payload' => 'required',

        ],
    ];
}
