<?php

namespace VCComponent\Laravel\Post\Validators;

use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use VCComponent\Laravel\Post\Entities\PostSchema;
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
            'published_date' => ['required'],
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
        $rules  = null;
        $schema = $this->getSchemaFunction($entity, $type);
        if ($schema) {
            $rules = $schema->map(function ($item) {
                return $item['rule'];
            })->toArray();
        }
        return $rules;
    }

    public function getNoRuleFields($entity, $type)
    {
        $fields = null;
        $schema = $this->getSchemaFunction($entity, $type);
        if ($schema) {
            $fields = $schema->filter(function ($item) {
                return count($item['rule']) === 0;
            })->toArray();
        }
        return $fields;
    }

    private function getSchemaFunction($entity, $type)
    {
        $schema = PostSchema::where('post_type', $type.' ')->with('schemaType')->with('schemaRule')->get()->mapWithKeys(function ($post) {
            return [$post->name => [
                'type' => $post->schemaType->name,
                'label' => $post->label,
                'rule' => []
            ]];
        });
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
