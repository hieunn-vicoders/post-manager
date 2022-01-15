<?php

use Faker\Generator as Faker;
use VCComponent\Laravel\Post\Entities\PostBlock;
use VCComponent\Laravel\Post\Test\Stubs\Models\WithSchemaAttributes\Post as PostWithSchemaAttributes;
use Carbon\Carbon;

$factory->define(PostBlock::class, function (Faker $faker) {
    return [
        'post_id'       => rand(1, 31),
        'block' => json_encode(['key' => rand(1, 31)]),
    ];
});
