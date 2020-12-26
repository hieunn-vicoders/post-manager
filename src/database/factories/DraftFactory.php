<?php

use Faker\Generator as Faker;
use VCComponent\Laravel\Post\Entities\Draftable;

$factory->define(Draftable::class, function (Faker $faker) {
    return [
        'draftable_type' => $faker->words(rand(4, 7), true),
        'draftable_id'   => $faker->sentences(rand(4, 7), true),
        'payload'        => $faker->paragraphs(rand(4, 7), true),
    ];
});