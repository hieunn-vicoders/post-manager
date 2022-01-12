<?php

use Faker\Generator as Faker;
use VCComponent\Laravel\Post\Entities\Post;
use VCComponent\Laravel\Post\Test\Stubs\Models\WithSchemaAttributes\Post as PostWithSchemaAttributes;
use Carbon\Carbon;

$factory->define(Post::class, function (Faker $faker) {
    return [
        'title'       => $faker->words(rand(4, 7), true),
        'description' => $faker->sentences(rand(4, 7), true),
        'content'     => $faker->paragraphs(rand(4, 7), true),
        'status'      => 1,
        "order"       => rand(1, 31),
        "editor_type" => 1,
        "type"        => "posts",
    ];
});

$factory->define(PostWithSchemaAttributes::class, function (Faker $faker) {
    return [
        'title'       => $faker->words(rand(4, 7), true),
        'description' => $faker->sentences(rand(4, 7), true),
        'content'     => $faker->paragraphs(rand(4, 7), true),
    ];
});

$factory->state(Post::class, 'pages', function () {
    return [
        'type' => 'pages',
    ];
});

$factory->state(Post::class, 'about', function () {
    return [
        'type' => 'about',
    ];
});
