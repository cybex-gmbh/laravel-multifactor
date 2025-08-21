<?php


use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMode;
use Cybex\LaravelMultiFactor\Exceptions\InvalidEmailOnlyLoginConfigurationException;
use Cybex\LaravelMultiFactor\Tests\BaseTest;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Throws;
use Cybex\LaravelMultiFactor\MultiFactorServiceProvider;

class EmailOnlyModeTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    #[DataProvider('invalidEmailOnlyModeConfigurations')]
    public function testThrowsOnInvalidEmailOnlyModeConfiguration($mode, $forceMethod)
    {
        $this->expectException(InvalidEmailOnlyLoginConfigurationException::class);

        $this->configureMFA(mode: $mode, emailOnlyMode: true, forceMethod: $forceMethod);
        $this->reloadServiceProvider();
    }

    public static function invalidEmailOnlyModeConfigurations(): array
    {
        return [
            'force mode with invalid force method totp' => [
                'mode' => MultiFactorAuthMode::FORCE,
                'forceMethod' => MultiFactorAuthMethod::TOTP,
            ],
            'force method email with invalid mode required' => [
                'mode' => MultiFactorAuthMode::REQUIRED,
                'forceMethod' => MultiFactorAuthMethod::EMAIL,
            ],
        ];
    }

    #[DataProvider('emailOnlyModeUserMethodsProvider')]
    public function testLoginInEmailOnlyMode(array $allowedMethods, array $userMethods)
    {
        $this->configureMFA(mode: MultiFactorAuthMode::FORCE, allowedMethods: $allowedMethods, emailOnlyMode: true);
        $this->reloadServiceProvider();

        $user = $this->makeUser($userMethods);
        $response = $this->loginWithEmailAndRedirect($user);

        $this->assertMFARedirectToExpectedRoute($userMethods, $response, MultiFactorAuthMethod::EMAIL);

        $this->loginWithMFAMethod(MultiFactorAuthMethod::EMAIL, $user);

        $this->assertMultiFactorAuthenticated();
    }

    public static function emailOnlyModeUserMethodsProvider(): array
    {
        return [
            'has only unallowed method' => [
                'allowedMethods' => ['email'],
                'userMethods' => [MultiFactorAuthMethod::EMAIL],
            ],
            'has no methods configured' => [
                'allowedMethods' => ['email'],
                'userMethods' => [],
            ],
            'has multiple methods configured' => [
                'allowedMethods' => ['email'],
                'userMethods' => [MultiFactorAuthMethod::EMAIL, MultiFactorAuthMethod::TOTP],
            ],
        ];
    }

    #[DataProvider('emailOnlyModeWithSetupProvider')]
    public function testLoginInEmailOnlyModeWithSetup(array $allowedMethods, array $userMethods, ?MultiFactorAuthMethod $methodToLogin)
    {
        $this->configureMFA(mode: MultiFactorAuthMode::FORCE, allowedMethods: $allowedMethods, emailOnlyMode: true);
        $this->reloadServiceProvider();

        $user = $this->makeUser($userMethods);
        $response = $this->loginWithEmailAndRedirect($user);

        $this->assertMFARedirectToExpectedRoute($userMethods, $response, $methodToLogin);

        $response = $this->loginWithMFAMethod($methodToLogin, $user);

        $this->followRedirects($response);

        $this->assertMFARedirectToExpectedRoute($userMethods, $response, MultiFactorAuthMethod::EMAIL);

        $this->loginWithMFAMethod(MultiFactorAuthMethod::EMAIL, $user);

        $this->assertMultiFactorAuthenticated();
    }

    public static function emailOnlyModeWithSetupProvider(): array
    {
        return [
            'has only unallowed method' => [
                'allowedMethods' => ['email'],
                'userMethods' => [MultiFactorAuthMethod::TOTP],
                'methodToLogin' => MultiFactorAuthMethod::TOTP,
            ],
        ];
    }

    public function reloadServiceProvider(): void
    {
        (new MultiFactorServiceProvider($this->app))->boot();
        $this->app->make('router')->getRoutes()->refreshNameLookups();
    }
}
