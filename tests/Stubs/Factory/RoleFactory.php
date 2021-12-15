<?php

use Faker\Generator as Faker;
use Illuminate\Support\Facades\Hash;
use NF\Roles\Models\Role;

$factory->define(Role::class, function (Faker $faker) {
    return [
        'name'        => $faker->name,
        'slug'     => $faker->slug,
    ];
});
