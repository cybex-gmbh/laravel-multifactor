<?php

use Cybex\LaravelMultiFactor\Http\Responses\MultiFactorChallengeViewResponse;
use Cybex\LaravelMultiFactor\Http\Responses\MultiFactorChooseViewResponse;
use Cybex\LaravelMultiFactor\Http\Responses\MultiFactorLoginViewResponse;
use Cybex\LaravelMultiFactor\Http\Responses\MultiFactorSettingsViewResponse;

return [
    // supported methods: 'email'
    'allowedMethods' => [
        'email',
    ],
    // 'optional' or 'required' or 'force'
    'mode' => env('MULTI_FACTOR_AUTHENTICATION_MODE', 'optional'),
    'forceMethod' => env('MULTI_FACTOR_AUTHENTICATION_FORCE_METHOD', 'email'),

    'views' => [
        'challenge' => MultiFactorChallengeViewResponse::class,
        'login' => MultiFactorLoginViewResponse::class,
        'choose' => MultiFactorChooseViewResponse::class,
        'settings' => MultiFactorSettingsViewResponse::class,
    ],

    'features' => [
        'email-login' => [
            'enabled' => env('MULTI_FACTOR_AUTHENTICATION_EMAIL_ONLY_LOGIN', false),
            'routePath' => 'mfa/email-login',
            'applicationLoginRouteName' => 'login',
        ],
        'settings' => [
            'enabled' => env('MULTI_FACTOR_AUTHENTICATION_SETTINGS', true),
            'routePath' => 'mfa/user/{user}/settings',
        ],
    ],
];
