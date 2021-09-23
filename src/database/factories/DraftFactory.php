<?php

use Faker\Generator as Faker;
use VCComponent\Laravel\Post\Entities\Draftable;

$factory->define(Draftable::class, function (Faker $faker) {
    return [
        'draftable_type' => 'posts',
        'draftable_id'   => 1,
        'payload'        => $faker->paragraphs(rand(4, 7), true),
    ];
});