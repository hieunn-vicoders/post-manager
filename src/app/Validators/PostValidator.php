<?php

namespace VCComponent\Laravel\Post\Validators;

use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use VCComponent\Laravel\Post\Validators\PostValidatorInterface;
use VCComponent\Laravel\Vicoders\Core\Validators\AbstractValidator;
use VCComponent\Laravel\Vicoders\Core\Validators\ValidatorInterface;

class PostValidator extends AbstractValidator implements PostValidatorInterface
{
    protected $rules = [
        ValidatorInterface::RULE_ADMIN_CREATE      => [
            'title'       => ['required'],
            'description' => [],
            'content'     => ['required'],
        ],
        ValidatorInterface::RULE_ADMIN_UPDATE      => [
            'title'       => ['required'],
            'description' => [],
            'content'     => ['required'],
        ],
        ValidatorInterface::RULE_ADMIN_UPDATE_DATE => [
            'post_date' => ['required'],
        ],
        ValidatorInterface::RULE_CREATE            => [
            'title'       => ['required'],
            'description' => [],
            'content'     => ['required'],
        ],
        ValidatorInterface::RULE_UPDATE            => [
            'title'       => ['required'],
            'description' => [],
            'content'     => ['required'],
        ],
        ValidatorInterface::BULK_UPDATE_STATUS     => [
            'ids'    => ['required'],
            'status' => ['required'],
        ],
        ValidatorInterface::UPDATE_STATUS_ITEM     => [
            'status' => ['required'],
        ],
        "RULE_IDS"                                 => [
            'ids'  => ['array', 'required'],
            'ids*' => ['integer'],
        ],
    ];

    public function getSchemaRules($entity, $type)
    {
        $schema = $this->getSchemaFunction($entity, $type);

        $rules = $schema->map(function ($item) {
            return $item['rule'];
        });

        return $rules->toArray();
    }

    public function getNoRuleFields($entity, $type)
    {
        $schema = $this->getSchemaFunction($entity, $type);

        $fields = $schema->filter(function ($item) {
            return count($item['rule']) === 0;
        });

        return $fields->toArray();
    }

    private function getSchemaFunction($entity, $type)
    {
        if (count($entity->postTypes()) && $type !== 'posts') {
            $schema_func = Str::camel($type . '_schema');
            $schema      = collect($entity->$schema_func());
        } elseif ($type == 'posts') {
            $schema = collect($entity->schema());
        }

        return $schema;
    }

    public function isSchemaValid($data, $rules)
    {
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new Exception($validator->errors(), 1000);
        }
        return true;
    }
}
