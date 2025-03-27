<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'allowedMethods' => [
        'email',
    ],
    // 'optional' or 'required' or 'force'
    'mode' => env('TWO_FACTOR_AUTHENTICATION_MODE', 'force'),
    'forceMethod' => env('TWO_FACTOR_AUTHENTICATION_FORCE_METHOD', 'email'),
];
