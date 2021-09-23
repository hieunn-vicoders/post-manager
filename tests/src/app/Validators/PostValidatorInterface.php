<?php

namespace VCComponent\Laravel\Post\Validators;

interface PostValidatorInterface
{
    public function getSchemaRules($entity, $type);

    public function getNoRuleFields($entity, $type);

    public function isSchemaValid($data, $rules);
}
