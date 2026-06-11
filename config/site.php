<?php

return [

    'contact' => [
        'name' => env('SITE_CONTACT_NAME'),
        'tagline' => env('SITE_CONTACT_TAGLINE', 'Fotografia de basquetebol e espetáculos de dança'),
        'email' => env('SITE_CONTACT_EMAIL'),
        'phone' => env('SITE_CONTACT_PHONE'),
        'location' => env('SITE_CONTACT_LOCATION'),
        'instagram' => env('SITE_CONTACT_INSTAGRAM'),
        'facebook' => env('SITE_CONTACT_FACEBOOK'),
    ],

    'user' => [
        'name' => env('USER_NAME'),
        'email' => env('USER_EMAIL'),
        'password' => env('USER_PASSWORD'),
    ],

];
