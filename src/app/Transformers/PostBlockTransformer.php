<?php

namespace VCComponent\Laravel\Post\Transformers;

use League\Fractal\TransformerAbstract;

class PostBlockTransformer extends TransformerAbstract
{

    public function transform($model)
    {
        return [
            'id'             => $model->id,
            'block'         => json_decode($model->block),
            'timestamps'     => [
                'created_at' => $model->created_at,
                'updated_at' => $model->updated_at,
            ],
        ];
    }
}
