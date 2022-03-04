<?php

/*
 * Config for API-integration
 */
return [
    'api' => [
        'url' => 'https://api.glesys.com/',
        'token' => env( 'GLESYS_TOKEN', '' ),
        'user' => env( 'GLESYS_USER', '' ),
        'timeout' => 30,
    ],
];