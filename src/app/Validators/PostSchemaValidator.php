<?php

namespace VCComponent\Laravel\Post\Validators;

use VCComponent\Laravel\Vicoders\Core\Validators\AbstractValidator;

class PostSchemaValidator extends AbstractValidator
{
    protected $rules = [
        'RULE_ADMIN_CREATE' => [
            'label'          => ['required'],
            'schema_type_id' => ['required'],
            'schema_rule_id' => ['required'],
            'post_type'      => ['required'],
        ],
        'RULE_ADMIN_UPDATE' => [

        ],
    ];
}
