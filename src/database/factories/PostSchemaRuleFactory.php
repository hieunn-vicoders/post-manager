<?php

use Faker\Generator as Faker;
use VCComponent\Laravel\Post\Entities\PostSchemaRule;

$factory->define(PostSchemaRule::class, function (Faker $faker) {
    return [
        'name'           => $faker->word(2),
    ];
});