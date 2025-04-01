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
];
