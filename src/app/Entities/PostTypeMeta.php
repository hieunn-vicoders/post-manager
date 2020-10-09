<?php

namespace VCComponent\Laravel\Post\Entities;

use Illuminate\Database\Eloquent\Model;

class PostTypeMeta extends Model
{
    protected $table = "post_type_meta";
    public function postTypes()
    {
        return [
            'about'       => [
                'router_slug' => 'about',
                'meta'        => [
                    'banner'      => [
                        'type'  => 'file',
                        'label' => 'Ảnh bìa',
                        'rules' => [],
                    ],
                    'rate'        => [
                        'type'  => "",
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
            'feeshipping' => [
                'router_slug' => 'about',
                'meta'        => [
                    'banner'      => [
                        'type'  => 'file',
                        'label' => 'Ảnh bìa',
                        'rules' => [],
                    ],
                    'rate'        => [
                        'type'  => "",
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
