<?php

namespace VCComponent\Laravel\Post\Transformers;

use League\Fractal\TransformerAbstract;

class PostTypeMetaTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        return [
            "id" => $model->id,
            "type" => $model->type,
            "key" => $model->key,
            "value" => $model->value,
            "timestamp" => [
                "created_at" => $model->created_at,
                "updated_at" => $model->updated_at,
            ]
        ];
    }

}
