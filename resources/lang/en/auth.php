<?php

return [
    'title' => 'Multi-Factor Authentication',
    'remember_me' => 'Remember me',

    'settings' => [
        'title' => 'Manage Multi-Factor Authentication',
        'subtitle' => 'You can enable or disable multi-factor authentication for your account.',
    ],
    'status' => [
        'enabled' => [
            'message' => 'Multi-Factor Authentication is enabled.',
            'label' => 'Enabled',
        ],
        'disabled' => [
            'message' => 'Multi-Factor Authentication is disabled.',
            'label' => 'Disabled',
        ],
    ],
    'setup' => [
        'subtitle' => 'Choose one of these methods to setup:',
    ],
    'choose' => [
        'title' => 'Choose Multi-Factor Auth Method',
        'subtitle' => 'Choose one of these methods to :action:',
    ],
    'email_login' => [
        'title' => 'Email Login',
    ],
    'email_challenge' => [
        'subtitle' => 'An email with an authentication :authenticationMethod was just sent to <strong>:email</strong>.',
        'subtitle_resend' => 'Didn\'t receive your :authenticationMethod?',
    ],
];