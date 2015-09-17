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

$factory->define(App\User::class, function ($faker) {
    return [
        'id' => '1',
        'name' => 'admin',
        'password' => bcrypt(env('DEFAULT_PASSWORD')),
        'status' => '0',
        'isadmin' => '0',
        'remember_token' => str_random(10),
    ];
});


$factory->define(App\AuthInfo::class, function ($faker) {
    return [
        'userid' => '1',
        'flag' => env('AUTH_TYPE_CMDB'),
        'key' => json_encode([
            'username' => 'admin',
            'password' => Crypt::encrypt(env('DEFAULT_PASSWORD')),
        ])
    ];
});
