<?php

return [
    'guard' => 'web',
    'passwords' => 'users',
    'username' => 'phone_number',
    'email' => 'email',
    'lowercase_usernames' => false,
    'home' => '/dashboard',
    'prefix' => '',
    'domain' => null,
    'middleware' => ['web'],
    'limiters' => [
        'login' => 'login',
    ],
    'views' => false,
    'features' => [],
];
