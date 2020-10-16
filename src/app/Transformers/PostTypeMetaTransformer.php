<?php

namespace VCComponent\Laravel\Post\Transformers;

use League\Fractal\TransformerAbstract;

class PostTypeMetaTransformer extends TransformerAbstract
{
    public function transform($model)
    {
        $transform = [
            $model->type => [
                $model->key => $model->value
            ]
        ];
        return $transform;
    }
}

