<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Models\Member::class, function (Faker\Generator $faker) {
    
    return [
        'account' => rand(),
        'password' => password_hash($faker->password, PASSWORD_DEFAULT),
        'user_name' => $faker->name,
        'supervisor_id' => $faker->numberBetween(0, 1000000),
        'created_at' => $faker->dateTimeBetween('-6 months', 'now'),
    ];
});
