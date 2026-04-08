<?php

namespace Ixopay\Client\Laravel;

use Ixopay\Client\Callback\Result;
use Ixopay\Client\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CallbackRequest
{
    public function __construct(private readonly Client $client)
    {
    }

    public function isValid(Request $request): bool
    {
        $signature = $request->header('X-Signature')
            ?? $request->header('Authorization')
            ?? '';

        $dateHeader = $request->header('Date')
            ?? $request->header('X-Date')
            ?? '';

        return $this->client->validateCallback(
            $request->getContent(),
            '/' . ltrim($request->getRequestUri(), '/'),
            $dateHeader,
            $signature
        );
    }

    public function parse(Request $request): Result
    {
        return $this->client->readCallback($request->getContent());
    }

    public function acknowledge(): Response
    {
        return response('OK');
    }
}
