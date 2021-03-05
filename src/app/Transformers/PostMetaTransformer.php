<?php

namespace VCComponent\Laravel\Post\Transformers;

use League\Fractal\TransformerAbstract;

class PostMetaTransformer extends TransformerAbstract
{

    public function transform($model)
    {
        return [
            'id'    => (int) $model->id,
            'key'   => $model->key,
            'value' => $model->value,
            'timestamps' => [
                'created_at' => $model->created_at,
                'updated_at' => $model->updated_at,
            ],
        ];
    }
}
