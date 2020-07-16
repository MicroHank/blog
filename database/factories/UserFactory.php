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
$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
    $password = config('henwen.seeder.user.default_password', '123456') ;
    
    $faker->addProvider(new \Faker\Provider\en_US\Person($faker));

    return [
        'name' => $faker->firstName,
        'email' => $faker->unique()->safeEmail,
        'password' => bcrypt($password),
        'remember_token' => str_random(10),
        'status' => $faker->randomElement([0, 1]),
    ];
});
