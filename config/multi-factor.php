<?php

use Cybex\LaravelMultiFactor\Http\Responses\MultiFactorChallengeViewResponse;
use Cybex\LaravelMultiFactor\Http\Responses\MultiFactorChooseViewResponse;
use Cybex\LaravelMultiFactor\Http\Responses\MultiFactorLoginViewResponse;
use Cybex\LaravelMultiFactor\Http\Responses\MultiFactorSettingsViewResponse;
use Cybex\LaravelMultiFactor\Http\Responses\MultiFactorSetupViewResponse;

return [
    // supported methods: 'email'
    'allowedMethods' => [
        'totp',
        'email',
    ],
    // 'optional' or 'required' or 'force'
    'mode' => env('MULTI_FACTOR_AUTHENTICATION_MODE', 'optional'),
    'forceMethod' => env('MULTI_FACTOR_AUTHENTICATION_FORCE_METHOD', 'email'),

    'views' => [
        'responses' => [
            'challenge' => MultiFactorChallengeViewResponse::class,
            'login' => MultiFactorLoginViewResponse::class,
            'choose' => MultiFactorChooseViewResponse::class,
            'settings' => MultiFactorSettingsViewResponse::class,
            'setup' => MultiFactorSetupViewResponse::class,
        ],
        'templates' => [
            'login' => 'auth.login',
            'confirm-password' => 'auth.confirm-password',
            'totp-challenge' => 'auth.two-factor-challenge',
        ],
    ],

    'features' => [
        'email-login' => [
            'enabled' => env('MULTI_FACTOR_AUTHENTICATION_EMAIL_ONLY_LOGIN', false),
            'routePath' => 'mfa/email-login',
        ],
        'settings' => [
            'routePath' => 'mfa/user/{user}/settings',
        ],
    ],
];
