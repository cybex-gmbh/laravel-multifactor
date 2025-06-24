# Laravel Multi-Factor

[![Latest Version on Packagist](https://img.shields.io/packagist/v/cybex/laravel-multi-factor.svg?style=flat-square)](https://packagist.org/packages/cybex/laravel-multifactor)
[![Total Downloads](https://img.shields.io/packagist/dt/cybex/laravel-multi-factor.svg?style=flat-square)](https://packagist.org/packages/cybex/laravel-multifactor)

This package provides a flexible multi-factor authentication solution for Laravel, supporting multiple methods and configuration of multi-factor authentication modes like force, required or optional.

## Features

- Supports multiple two-factor authentication methods (e.g., email).
- Configurable modes: `optional`, `required`, or `force`.
- Customizable views and routes.
- Email-only login with one-time codes or links.

### Multi-factor Modes

- **Optional**: Users can enable MFA in their profile settings.
- **Required**: MFA must be set up upon login.
- **Force**: MFA is enforced for all users with a specified method.

**Supported Methods:**
- **Email**: Users receive a login URL or one-time code via email.

### Email-Only Login

Users can log in using a unique email link, enabling authentication with just their email address.

## Requirements

- PHP 8.1 or higher
- Laravel 9.x or higher

## Installation

```bash
composer require cybex/laravel-multi-factor
```

### Migrating

```bash
php artisan migrate
```

### Publish the Configuration File

```bash
php artisan vendor:publish --provider="CybexGmbh\LaravelMultiFactor\LaravelMultiFactorServiceProvider" --tag="multi-factor.config"
```

### Configuration

Open the `config/multi-factor.php` file and adjust the settings as needed:

- **`allowedMethods`**: Define the multi-factor methods you want to support (e.g., `email`).
- **`mode`**: Set the mode to `optional`, `required`, or `force`.
- **`forceMethod`**: Specify the method to use when the mode is set to `force`.

### Add Environment Variables

```env
MULTI_FACTOR_AUTHENTICATION_MODE=optional
MULTI_FACTOR_AUTHENTICATION_FORCE_METHOD=email
MULTI_FACTOR_AUTHENTICATION_EMAIL_ONLY_LOGIN=true
```

### Apply Middlewares

Guard your applications routes with multi-factor authentication using these middlewares:

```php
Route::middleware(['hasMultiFactorAuthentication', 'hasAllowedMultiFactorAuthMethods'])->group(function () {
});
```

### Multi-Factor Authentication Trait

Implement the `MultiFactorAuthTrait` in your `User` model:

```php
use CybexGmbh\LaravelMultiFactor\Traits\MultiFactorAuthTrait;

class User extends Authenticatable
{
    use MultiFactorAuthTrait;
}
```

### Mail Server

To use email authentication, configure your mail server in the `.env` file. For example:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
```

### Email Only Login

Set your application's login route name in the multi-factor configuration:

```php
'features' => [
    'email-login' => [
        'applicationLoginRouteName' => 'login',
    ],
],
```

To disable email only login set the `MULTI_FACTOR_AUTHENTICATION_EMAIL_ONLY_LOGIN` env variable to `false`:

```env
MULTI_FACTOR_AUTHENTICATION_EMAIL_ONLY_LOGIN=false
```

### Settings Page

To allow users to manage their multi-factor authentication methods, add a link to the `mfa.settings` route:

```php
<a href="{{ route('mfa.settings') }}">Manage Multi-Factor Authentication</a>
```

You can disable the settings page via the `MULTI_FACTOR_AUTHENTICATION_SETTINGS` env variable:

```env
MULTI_FACTOR_AUTHENTICATION_SETTINGS=false
```

### Customizing Views (Optional)

The package provides default views for multi-factor authentication. You can customize them by publishing the views:

```bash
php artisan vendor:publish --provider="CybexGmbh\LaravelMultiFactor\LaravelMultiFactorServiceProvider" --tag="multi-factor.views"
```

You can find the views in the `resources/views/vendor/laravel-multi-factor` directory.
You can find the views in the `resources/views/vendor/laravel-multi-factor/pages` directory.

### Customizing Routes (Optional)

The package provides default routes for multi-factor authentication. You can customize the `path` in the `config/multi-factor.php` file under the `features.feature.routePath` key:

```php
'features' => [
    'settings' => [
        'routePath' => 'mfa/settings',
    ],
],
```

## Usage

### Enabling Multi-Factor Authentication for a User

To enable multi-factor authentication for a user, call the `setup` method of the handler:

```php
MultiFactorAuthMethod::method->getHandler()->setup();
```

You can pass an optional user instance to the `setup` method. If no user is provided, the currently authenticated user will be used:

```php
MultiFactorAuthMethod::method->getHandler()->setup($user);
```

### Customizing Multi-Factor Methods

You can create custom handlers by implementing the `MultiFactorAuthMethod` interface and registering them in the `getHandler` method of the `MultiFactorAuthMethod` enum.

## Configuration Options

The `config/multi-factor.php` file includes the following options:

- **`allowedMethods`**: List of allowed two-factor methods.
- **`mode`**: Mode of multi-factor authentication (`optional`, `required`, or `force`).
- **`forceMethod`**: The method to use when `mode` is set to `force`.
- **`views`**: Customizable views for different multi-factor flows.
- **`routes`**: Configurable routes for multi-factor authentication.


- **`routes.emailOnlyLogin`**: Users can log in using only their email address. 

## Adding a New Multi-Factor Authentication Method

To add a new multi-factor authentication method to the package, follow these steps:

1. Create a new handler class that implements the `MultiFactorAuthMethodHandlerContract` interface in `src/Classes/MultiFactorAuthmethodHandler/`. This handler will define the logic for setting up, sending, and authenticating the new method.

2. Add the new method to the `MultiFactorAuthMethod` enum:

```php
namespace CybexGmbh\LaravelMultiFactor\Enums;

use App\MultiFactor\CustomMultiFactorHandler;

enum MultiFactorAuthMethod: string
{
case EMAIL = 'email';
case TOTP = 'totp';
case CUSTOM = 'custom';

    public function getHandler(): MultiFactorAuthMethodHandlerContract
    {
        return match ($this) {
            self::EMAIL => new EmailHandler(),
            self::TOTP => new TotpHandler(),
            self::CUSTOM => new CustomMultiFactorHandler(),
        };
    }
}
```


3. Add the new method to the `allowedMethods` array in the `config/multi-factor.php` file:

```php
'allowedMethods' => [
    'customMethod',
],
```

### Customizing Views (Optional)

If the new method requires custom views, create a new View Response Contract that extends the Responsable Interface, make a new Response Class, and implement the `MultiFactorAuthMethodResponseContract` interface.

Put the new view in the `resources/views/` directory. In the `config/multi-factor.php` file, add the new view to the `views` array.

Add the new Response Class to the `MultiFactorServiceProvider's` `register` method:

```php
$this->app->singleton(MultiFactorCustomViewResponseContract::class, fn($app, $params): MultiFactorCustomViewResponseContract => new (config('multi-factor.views.customView'))(...$params));
```

To use the new view:

```php
return app(MultiFactorCustomViewResponseContract::class, $params);
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for recent changes.

## Credits

-   [Fabian Holy](https://github.com/holyfabi)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
