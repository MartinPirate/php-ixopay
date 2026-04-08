# Local Sandbox Example

This example adds a lightweight local sandbox that behaves more like a Stripe
or PayPal-style test environment: you point the SDK at a non-production URL and
run transactions against predictable scenarios.

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

## What it provides

- transaction endpoints:
  - `POST /api/v3/transaction/{apiKey}/debit`
  - `POST /api/v3/transaction/{apiKey}/preauthorize`
- status lookup:
  - `GET /api/v3/status/{apiKey}/getByMerchantTransactionId/{merchantTransactionId}`
- callback payload endpoint:
  - `GET /sandbox/callback/{merchantTransactionId}`

## How scenarios work

The SDK example sets a custom request header:

```php
$client->setCustomRequestHeaders([
    'X-Sandbox-Scenario' => 'redirect',
]);
```

The local sandbox reads that header and returns a predictable result shape that
the existing SDK parser can consume without any SDK changes.
