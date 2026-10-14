<?php

namespace Tests\Feature;

use Orchestra\Testbench\TestCase;
use ReflectionProperty;
use TomatoPHP\LaravelAgora\LaravelAgoraServiceProvider;
use TomatoPHP\LaravelAgora\Services\Agora;
use TomatoPHP\LaravelAgora\Services\Token\AccessToken;

class AgoraTokenExpiryTest extends TestCase
{
    private const APP_ID = '970CA35de60c44645bbae8a215061b33';

    private const CERTIFICATE = '5CFd2fd1755d40ecb72977518be15d3b';

    protected function getPackageProviders($app): array
    {
        return [LaravelAgoraServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('laravel-agora.agora.app_id', self::APP_ID);
        $app['config']->set('laravel-agora.agora.app_certificate', self::CERTIFICATE);
    }

    /**
     * Decode the privileges (name => expiry timestamp) out of a generated token.
     */
    private function privilegesOf(string $token, string $channel, string $uid = ''): array
    {
        $decoded = AccessToken::initWithToken($token, self::CERTIFICATE, $channel, $uid);
        $this->assertNotNull($decoded, 'The token could not be decoded.');

        $message = new ReflectionProperty($decoded, 'message');
        $message->setAccessible(true);

        return $message->getValue($decoded)->privileges;
    }

    public function test_default_expiry_is_one_hour(): void
    {
        $this->assertSame(3600, Agora::make(1)->getExpireIn());

        $privileges = $this->privilegesOf(Agora::make(1)->channel('call')->token(), 'call.1');
        $expiry = $privileges[AccessToken::$privileges['kJoinChannel']];

        $this->assertEqualsWithDelta(time() + 3600, $expiry, 5);
    }

    public function test_expiry_can_be_set_in_config(): void
    {
        config()->set('laravel-agora.agora.expire', 600);

        $this->assertSame(600, Agora::make(1)->getExpireIn());

        $privileges = $this->privilegesOf(Agora::make(1)->channel('call')->token(), 'call.1');
        $this->assertEqualsWithDelta(time() + 600, $privileges[AccessToken::$privileges['kJoinChannel']], 5);
    }

    public function test_expiry_can_be_set_per_token_and_overrides_config(): void
    {
        config()->set('laravel-agora.agora.expire', 600);

        $token = Agora::make(7)->channel('call')->expireIn(86400)->token();
        $privileges = $this->privilegesOf($token, 'call.7');

        $this->assertEqualsWithDelta(time() + 86400, $privileges[AccessToken::$privileges['kJoinChannel']], 5);
    }

    public function test_expiry_must_be_positive(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Agora::make(1)->expireIn(0);
    }
}
