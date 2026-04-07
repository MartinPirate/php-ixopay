<?php

namespace Ixopay\Client\Tests\Laravel;

use Ixopay\Client\Client;
use Ixopay\Client\Laravel\IxopayManager;
use Ixopay\Client\Tests\TestCase;
use Ixopay\Client\Laravel\CallbackRequest;

class IxopayServiceProviderTest extends TestCase
{
    public function test_it_registers_client_manager_and_callback_helper(): void
    {
        $this->assertInstanceOf(Client::class, $this->app->make(Client::class));
        $this->assertInstanceOf(IxopayManager::class, $this->app->make(IxopayManager::class));
        $this->assertInstanceOf(CallbackRequest::class, $this->app->make(CallbackRequest::class));
    }

    public function test_it_builds_client_from_config(): void
    {
        $client = $this->app->make(Client::class);

        $this->assertSame('user', $client->getUsername());
        $this->assertSame('api-key', $client->getApiKey());
        $this->assertSame('secret', $client->getSharedSecret());
        $this->assertSame('https://gateway.ixopay.com/', Client::getApiUrl());
    }

    public function test_facade_resolves_the_same_client_instance(): void
    {
        $client = $this->app->make(Client::class);

        $this->assertSame($client, \Ixopay::getFacadeRoot());
    }
}
