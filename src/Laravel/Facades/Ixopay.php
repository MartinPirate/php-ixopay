<?php

namespace Ixopay\Client\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

class Ixopay extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'ixopay.client';
    }
}
