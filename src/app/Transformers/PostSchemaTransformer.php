<?php

namespace VCComponent\Laravel\Post\Transformers;

use League\Fractal\TransformerAbstract;
use VCComponent\Laravel\Post\Entities\PostSchemaRule;
use VCComponent\Laravel\Post\Entities\PostSchemaType;
use VCComponent\Laravel\Post\Transformers\PostSchemaRuleTransformer;
use VCComponent\Laravel\Post\Transformers\PostSchemaTypeTransformer;

class PostSchemaTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'schemaRule',
        'schemaType',
    ];

    public function __construct($includes = [])
    {
        $this->setDefaultIncludes($includes);
    }

    public function transform($model)
    {
        return [
            'id'             => $model->id,
            'name'           => $model->name,
            'label'          => $model->label,
            'schema_type_id' => $model->schema_type_id,
            'schema_rule_id' => $model->schema_rule_id,
            'post_type'      => $model->post_type,
            'post_id'        => $model->post_id,
            "value"          => $model->value,
            'timestamps'     => [
                'created_at' => $model->created_at,
                'updated_at' => $model->updated_at,
            ],
        ];
    }

    public function includeSchemaType($model)
    {
        if ($model->schemaType) {
            return $this->item($model->schemaType, new PostSchemaTypeTransformer());
        }
    }

    public function includeSchemaRule($model)
    {
        if ($model->schemaRule) {
            return $this->item($model->schemaType, new PostSchemaRuleTransformer());
        }
    }
}
