<?php

/*
 * You can place your custom package configuration in here.
 */

use CybexGmbh\LaravelTwoFactor\Http\Responses\MultiFactorChooseViewResponse;
use CybexGmbh\LaravelTwoFactor\Http\Responses\MultiFactorDeleteViewResponse;
use CybexGmbh\LaravelTwoFactor\Http\Responses\MultiFactorLoginViewResponse;
use CybexGmbh\LaravelTwoFactor\Http\Responses\MultiFactorSettingsViewResponse;
use CybexGmbh\LaravelTwoFactor\Http\Responses\MultiFactorSetupViewResponse;
use CybexGmbh\LaravelTwoFactor\Http\Responses\MultiFactorChallengeViewResponse;

return [
    'allowedMethods' => [
        'email',
    ],
    // 'optional' or 'required' or 'force'
    'mode' => env('TWO_FACTOR_AUTHENTICATION_MODE', 'optional'),
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
            'path' => 'two-factor-auth/email-login',
        ],
        'settings' => [
            'enabled' => false,
            'path' => 'two-factor-auth/user/{user}/settings',
        ],
        'login' => [
            'name' => 'login',
        ]
    ],
];
