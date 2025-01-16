<?php

use Laravel\Sanctum\Sanctum;

return [

    'stateful' => [],

    'guard' => [],

    'expiration' => env('SANCTUM_TOKEN_EXPIRATION_MINUTES', 4320),

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', 'api_'),

    'middleware' => [],

];
