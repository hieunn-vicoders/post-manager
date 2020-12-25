<?php

use Faker\Generator as Faker;
use VCComponent\Laravel\Post\Entities\Post;
use VCComponent\Laravel\Post\Test\Stubs\Models\WithSchemaAttributes\Post as PostWithSchemaAttributes;

$factory->define(Post::class, function (Faker $faker) {
    return [
        'title'       => $faker->words(rand(4, 7), true),
        'description' => $faker->sentences(rand(4, 7), true),
        'content'     => $faker->paragraphs(rand(4, 7), true),
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
