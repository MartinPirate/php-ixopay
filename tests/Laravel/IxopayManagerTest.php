<?php

namespace Ixopay\Client\Tests\Laravel;

use InvalidArgumentException;
use Ixopay\Client\Laravel\IxopayManager;
use Ixopay\Client\Tests\TestCase;

class IxopayManagerTest extends TestCase
{
    public function test_it_requires_the_minimum_credentials(): void
    {
        $this->app['config']->set('ixopay.shared_secret', null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing IXOPAY configuration value [shared_secret].');

        $this->app->make(IxopayManager::class)->client();
    }
}
