<?php

use Faker\Generator as Faker;
use VCComponent\Laravel\Post\Entities\PostSchema;

$factory->define(PostSchema::class, function (Faker $faker) {
    return [
        'name'           => 'phone',
        'label'          => 'Phone',
        'schema_type_id' => 1,
        'schema_rule_id' => 5,
    ];
});

$factory->state(PostSchema::class, 'pages', function () {
    return [
        'post_type' => 'pages',
    ];
});

$factory->state(PostSchema::class, 'posts', function () {
    return [
        'post_type' => 'posts',
    ];
});

$factory->state(PostSchema::class, 'about', function () {
    return [
        'post_type' => 'about',
    ];
});
