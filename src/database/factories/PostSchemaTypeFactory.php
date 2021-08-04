<?php

use Faker\Generator as Faker;
use VCComponent\Laravel\Post\Entities\PostSchemaType;

$factory->define(PostSchemaType::class, function (Faker $faker) {
    return [
        'name'           => $faker->word(2),
    ];
});