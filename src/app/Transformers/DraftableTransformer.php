<?php

namespace VCComponent\Laravel\Post\Transformers;

use League\Fractal\TransformerAbstract;

class DraftableTransformer extends TransformerAbstract
{
    protected $availableIncludes = [];

    public function __construct($includes = [])
    {
        $this->setDefaultIncludes($includes);
    }

    public function transform($model)
    {
        return [
            'id'             => (int) $model->id,
            'draftable_type' => $model->draftable_type,
            'draftable_id'   => $model->draftable_id,
            'payload'        => $model->payload,
            'timestamps'     => [
                'created_at' => $model->created_at,
                'updated_at' => $model->updated_at,
            ],
        ];
    }
}
