<?php

namespace VCComponent\Laravel\Post\Transformers;

use League\Fractal\TransformerAbstract;

class PostBlockTransformer extends TransformerAbstract
{

    public function transform($model)
    {
        return [
            'id'             => $model->id,
            'blocks'         => $model->blocks,
            'timestamps'     => [
                'created_at' => $model->created_at,
                'updated_at' => $model->updated_at,
            ],
        ];
    }
}
