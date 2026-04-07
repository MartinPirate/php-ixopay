<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Ixopay\Client\Callback\Result as CallbackResult;
use Ixopay\Client\Laravel\CallbackRequest;

class CallbackController
{
    public function __construct(private readonly CallbackRequest $ixopayCallback)
    {
    }

    public function __invoke(Request $request): Response
    {
        abort_unless($this->ixopayCallback->isValid($request), 400, 'Invalid IXOPAY callback signature.');

        $callback = $this->ixopayCallback->parse($request);

        if ($callback->getResult() === CallbackResult::RESULT_OK) {
            // mark payment as successful
        } elseif ($callback->getResult() === CallbackResult::RESULT_ERROR) {
            // store the error information and update order state
        }

        return $this->ixopayCallback->acknowledge();
    }
}
