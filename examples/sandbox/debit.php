<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use Ixopay\Client\Client;
use Ixopay\Client\Data\Customer;
use Ixopay\Client\StatusApi\StatusRequestData;
use Ixopay\Client\Transaction\Debit;
use Ixopay\Client\Transaction\Result;

$scenario = $argv[1] ?? 'redirect';

Client::setApiUrl('http://127.0.0.1:8089/');

$client = new Client('sandbox-user', 'sandbox-password', 'sandbox-api-key', 'sandbox-shared-secret');
$client->setCustomRequestHeaders([
    'X-Sandbox-Scenario' => $scenario,
]);

$customer = (new Customer())
    ->setFirstName('Sandbox')
    ->setLastName('Tester')
    ->setEmail('sandbox@example.test')
    ->setIpAddress('127.0.0.1');

$merchantTransactionId = 'sandbox-' . date('YmdHis');

$transaction = (new Debit())
    ->setMerchantTransactionId($merchantTransactionId)
    ->setSuccessUrl('http://example.test/success')
    ->setCancelUrl('http://example.test/cancel')
    ->setCallbackUrl('http://example.test/callback')
    ->setAmount(10.5)
    ->setCurrency('EUR')
    ->setCustomer($customer);

$result = $client->debit($transaction);

echo 'Scenario: ' . $scenario . PHP_EOL;
echo 'Success: ' . ($result->isSuccess() ? 'true' : 'false') . PHP_EOL;
echo 'Return type: ' . $result->getReturnType() . PHP_EOL;
echo 'UUID: ' . $result->getUuid() . PHP_EOL;

if ($result->getReturnType() === Result::RETURN_TYPE_REDIRECT) {
    echo 'Redirect URL: ' . $result->getRedirectUrl() . PHP_EOL;
}

if ($result->getReturnType() === Result::RETURN_TYPE_ERROR) {
    foreach ($result->getErrors() as $error) {
        echo 'Error: ' . $error->getCode() . ' - ' . $error->getMessage() . PHP_EOL;
    }
}

$statusResult = $client->sendStatusRequest(
    (new StatusRequestData())->setMerchantTransactionId($merchantTransactionId)
);

echo 'Status lookup: ' . $statusResult->getTransactionStatus() . PHP_EOL;
echo 'Callback payload: http://127.0.0.1:8089/sandbox/callback/' . rawurlencode($merchantTransactionId) . PHP_EOL;
