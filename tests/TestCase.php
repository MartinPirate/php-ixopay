<?php

namespace Ixopay\Client\Tests;

use Ixopay\Client\Laravel\Facades\Ixopay;
use Orchestra\Testbench\TestCase as Orchestra;
use Ixopay\Client\Laravel\IxopayServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [IxopayServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Ixopay' => Ixopay::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('ixopay', [
            'username' => 'user',
            'password' => 'password',
            'api_key' => 'api-key',
            'shared_secret' => 'secret',
            'language' => 'en',
            'gateway_url' => 'https://gateway.ixopay.com/',
            'custom_request_headers' => [],
            'custom_curl_options' => [],
        ]);
    }
}
