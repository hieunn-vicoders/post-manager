<?php

namespace VCComponent\Laravel\Post\Entities;

use Illuminate\Database\Eloquent\Model;

class PostTypeMeta extends Model
{
    protected $table = "post_type_meta";
    public function postTypes()
    {
        return [
            'post-type' => [
                'router_slug' => 'promotion',
                'meta'        => [
                    'banner'      => [
                        'type'  => 'file',
                        'label' => 'Ảnh bìa',
                        'rules' => [],
                    ],
                    'rate'        => [
                        'type'  => "integer",
                        'label' => 'Đánh giá',
                        'rules' => [],
                    ],
                    "description" => [
                        "type"  => "string",
                        "label" => "Mô tả",
                        "rules" => [],
                    ],
                ],
            ],
        ];
    }

}
