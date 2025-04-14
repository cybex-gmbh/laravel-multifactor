<?php

/*
 * You can place your custom package configuration in here.
 */

use CybexGmbh\LaravelMultiFactor\Http\Responses\MultiFactorChallengeViewResponse;
use CybexGmbh\LaravelMultiFactor\Http\Responses\MultiFactorChooseViewResponse;
use CybexGmbh\LaravelMultiFactor\Http\Responses\MultiFactorDeleteViewResponse;
use CybexGmbh\LaravelMultiFactor\Http\Responses\MultiFactorLoginViewResponse;
use CybexGmbh\LaravelMultiFactor\Http\Responses\MultiFactorSettingsViewResponse;
use CybexGmbh\LaravelMultiFactor\Http\Responses\MultiFactorSetupViewResponse;

return [
    'allowedMethods' => [
        'email',
        'totp',
    ],
    // 'optional' or 'required' or 'force'
    'mode' => env('TWO_FACTOR_AUTHENTICATION_MODE', 'required'),
    'forceMethod' => env('TWO_FACTOR_AUTHENTICATION_FORCE_METHOD', 'email'),

    'views' => [
        'challenge' => MultiFactorChallengeViewResponse::class,
        'login' => MultiFactorLoginViewResponse::class,
        'setup' => MultiFactorSetupViewResponse::class,
        'choose' => MultiFactorChooseViewResponse::class,
        'delete' => MultiFactorDeleteViewResponse::class,
        'settings' => MultiFactorSettingsViewResponse::class,
    ],

    'routes' => [
        'email-login' => [
            'enabled' => env('TWO_FACTOR_AUTHENTICATION_EMAIL_ONLY_LOGIN', true),
            'path' => '2fa/email-login',
        ],
        'settings' => [
            'enabled' => true,
            'path' => '2fa/user/{user}/settings',
        ],
        'login' => [
            'name' => 'login',
        ]
    ],
];
