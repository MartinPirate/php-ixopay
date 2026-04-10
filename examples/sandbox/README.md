# Local Sandbox Example

This example adds a lightweight local sandbox that behaves more like a Stripe
or PayPal-style test environment: you point the SDK at a non-production URL and
run transactions against predictable scenarios.

It is intentionally separate from receipt templates. This sandbox is for
testing payment behavior, callbacks, and status transitions.

## Start the local sandbox

From the repository root:

```bash
php -S 127.0.0.1:8089 examples/sandbox/router.php
```

## Run a debit against the sandbox

In a second terminal:

```bash
php examples/sandbox/debit.php redirect
```

Supported scenarios:

- `redirect`
- `pending`
- `finished`
- `error`
- `card-3ds`
- `mpesa-success`
- `mpesa-pending`
- `mpesa-timeout`

You can also pass the payment method explicitly:

```bash
php examples/sandbox/debit.php card-3ds card
php examples/sandbox/debit.php mpesa-pending mpesa
```

## Test credentials

```text
Base URL: http://127.0.0.1:8089/
API key: sandbox-api-key
Username: sandbox-user
Password: sandbox-password
Shared secret: sandbox-shared-secret
```

Or fetch them from the sandbox:

```bash
curl http://127.0.0.1:8089/sandbox/test-credentials
```

## Test payment methods

```bash
curl http://127.0.0.1:8089/sandbox/payment-methods
```

The sandbox currently exposes card and M-Pesa-style mobile money test methods.

## What it provides

- transaction endpoints:
  - `POST /api/v3/transaction/{apiKey}/debit`
  - `POST /api/v3/transaction/{apiKey}/preauthorize`
- status lookup:
  - `GET /api/v3/status/{apiKey}/getByMerchantTransactionId/{merchantTransactionId}`
- callback payload endpoint:
  - `GET /sandbox/callback/{merchantTransactionId}`
- webhook replay endpoint:
  - `POST /sandbox/webhooks/{merchantTransactionId}/replay`

## How scenarios work

The SDK example sets a custom request header:

```php
$client->setCustomRequestHeaders([
    'Authorization' => 'Basic ' . base64_encode('sandbox-user:sandbox-password'),
    'X-Sandbox-Scenario' => 'redirect',
]);
```

The local sandbox reads that header and returns a predictable result shape that
the existing SDK parser can consume without any SDK changes.

For M-Pesa-style testing:

```php
$client->setCustomRequestHeaders([
    'Authorization' => 'Basic ' . base64_encode('sandbox-user:sandbox-password'),
    'X-Sandbox-Payment-Method' => 'mpesa',
    'X-Sandbox-Scenario' => 'mpesa-pending',
]);
```
