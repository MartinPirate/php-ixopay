<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Ixopay\Client\Client;
use Ixopay\Client\Data\Customer;
use Ixopay\Client\Transaction\Debit;
use Ixopay\Client\Transaction\Error;
use Ixopay\Client\Transaction\Result;

class CheckoutController
{
    public function __construct(private readonly Client $ixopay)
    {
    }

    public function debit(Request $request): RedirectResponse
    {
        $merchantTransactionId = (string) str()->uuid();

        $customer = (new Customer())
            ->setFirstName((string) $request->input('first_name'))
            ->setLastName((string) $request->input('last_name'))
            ->setEmail((string) $request->input('email'))
            ->setIpAddress((string) $request->ip());

        $transaction = (new Debit())
            ->setMerchantTransactionId($merchantTransactionId)
            ->setSuccessUrl(route('ixopay.success'))
            ->setCancelUrl(route('ixopay.cancel'))
            ->setCallbackUrl(route('ixopay.callback'))
            ->setAmount((float) $request->input('amount'))
            ->setCurrency((string) $request->input('currency', 'EUR'))
            ->setCustomer($customer);

        $result = $this->ixopay->debit($transaction);

        if (!$result->isSuccess()) {
            $errors = $result->getErrors();
            $message = $errors !== [] && $errors[0] instanceof Error
                ? $errors[0]->getMessage()
                : 'IXOPAY request failed.';

            abort(502, $message);
        }

        return match ($result->getReturnType()) {
            Result::RETURN_TYPE_REDIRECT => redirect()->away($result->getRedirectUrl()),
            Result::RETURN_TYPE_FINISHED => redirect()->route('ixopay.success'),
            Result::RETURN_TYPE_PENDING => redirect()->route('ixopay.pending'),
            default => redirect()->route('ixopay.cancel'),
        };
    }
}
