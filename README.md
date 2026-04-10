
# IXOPAY PHP SDK

<!-- shields -->
[![Packagist][packagist-shield]][packagist-url]
[![PHP Version][php-shield]][packagist-url]
[![License][license-shield]][license]

Accept payments and integrate 100+ payment methods on your PHP backend:
the [IXOPAY][ixopay] PHP SDK
provides convenient access to the [IXOPAY REST APIs][ixopay-docs-api].

<details>
  <summary>Table of Contents</summary>

<!-- TOC -->
- [IXOPAY PHP SDK](#ixopay-php-sdk)
  - [Installation](#installation)
    - [Requirements](#requirements)
    - [Composer](#composer)
  - [Documentation](#documentation)
  - [Recipe templates](#recipe-templates)
  - [Usage](#usage)
    - [Prerequisites](#prerequisites)
    - [Setting up credentials](#setting-up-credentials)
    - [Process a debit transaction](#process-a-debit-transaction)
    - [Local sandbox](#local-sandbox)
    - [Laravel integration](#laravel-integration)
  - [Application Notes](#application-notes)
  - [Support](#support)
  - [Licence](#licence)
  - [See also](#see-also)
<!-- TOC -->

</details>

## Installation

### Requirements

- PHP v8.1 or newer
- [Composer][composer]

### Composer

Add the IXOPAY PHP SDK to your `composer.json`.

```bash
composer require ixopay/ixopay-php-client
```

## Documentation

Please see [IXOPAY Gateway Documentation][ixopay-docs-gateway] for general
information about how to use the transaction processing API.

See the [IXOPAY API Reference][ixopay-docs-api] for a reference of all
transaction processing API calls.

## Recipe templates

This branch includes a working recipe-template generator for the Developer Hub
`Recipes -> How to ...` module. It turns structured templates from
[`tools/recipes/templates`](tools/recipes/templates) into Docusaurus-style recipe
pages under [`docs/recipes/how-to`](docs/recipes/how-to).

Build the recipes:

```bash
composer recipes:build
```

Included templates:

- `How to use receipt templates`
- `How to test with a local sandbox`
- `How to plan African payment method coverage`

## Usage

### Prerequisites

- [IXOPAY][ixopay] account
- API User - consisting of:
  - username, and
  - password
- Connector - consisting of:
  - API key, and
  - optional: shared secret

### Setting up credentials

Instantiate a new `Ixopay\Client\Client` authenticated via your API user & password,
connecting it to a payment adapter identified by an API key and authenticated using a shared secret.

```php
<?php

use Ixopay\Client\Client;
use Ixopay\Client\Data\Customer;
use Ixopay\Client\Transaction\Debit;
use Ixopay\Client\Transaction\Result;

// Instantiate the "Ixopay\Client\Client" with your credentials
$api_user = "your_username";
$api_password = "your_username";
$connector_api_key = "your_chosen_connector_api_key";
$connector_shared_secret = "your_generated_connector_shared_secret";
$client = new Client($api_user, $api_password, $connector_api_key, $connector_shared_secret);
```

### Process a debit transaction

Once you instantiated a [client with credentials](#setting-up-credentials),
you can use the instance to make transaction API calls.

```php
<?php

// define your transaction ID: e.g. 'myId-'.date('Y-m-d').'-'.uniqid()
$merchantTransactionId = 'your_transaction_id'; // must be unique

$customer = new Customer()
$customer = $customer
    ->setBillingCountry("AT")
    ->setEmail("customer@example.org");

// after the payment flow the user is redirected to the $redirectUrl
$redirectUrl = 'https://example.org/success';
// all payment state changes trigger the $callbackUrl hook
$callbackUrl = 'https://api.example.org/payment-callback';

$debit = new Debit();
$debit = $debit->setTransactionId($merchantTransactionId)
    ->setSuccessUrl($redirectUrl)
    ->setCancelUrl($redirectUrl)
    ->setCallbackUrl($callbackUrl)
    ->setAmount(10.00)
    ->setCurrency('EUR')
    ->setCustomer($customer);

// send the transaction
$result = $client->debit($debit);

// now handle the result
if ($result->isSuccess()) {
    //act depending on $result->getReturnType()

    $gatewayReferenceId = $result->getReferenceId(); //store it in your database

    if ($result->getReturnType() == Result::RETURN_TYPE_ERROR) {
        //error handling
        $errors = $result->getErrors();
        //cancelCart();

    } elseif ($result->getReturnType() == Result::RETURN_TYPE_REDIRECT) {
        //redirect the user
        header('Location: '.$result->getRedirectUrl());
        die;

    } elseif ($result->getReturnType() == Result::RETURN_TYPE_PENDING) {
        //payment is pending, wait for callback to complete

        //setCartToPending();

    } elseif ($result->getReturnType() == Result::RETURN_TYPE_FINISHED) {
        //payment is finished, update your cart/payment transaction

        //finishCart();
    }
}

?>
```

### Local sandbox

The repository also includes a lightweight local sandbox example that lets you
point the SDK at a local API URL and exercise predictable transaction outcomes
such as redirect, pending, finished, and error flows.

Start the sandbox:

```bash
php -S 127.0.0.1:8089 examples/sandbox/router.php
```

Run the demo debit:

```bash
php examples/sandbox/debit.php redirect
```

See [`examples/sandbox`](examples/sandbox) for details.

### Laravel integration

The SDK can also be wired into a Laravel application with a lightweight
service provider, config file, and facade.

#### 1. Install the package

```bash
composer require ixopay/ixopay-php-client
```

#### 2. Publish the config

```bash
php artisan vendor:publish --tag=ixopay-config
```

Or use the package install command:

```bash
php artisan ixopay:install
```

#### 3. Configure credentials

```env
IXOPAY_USERNAME=your_username
IXOPAY_PASSWORD=your_password
IXOPAY_API_KEY=your_api_key
IXOPAY_SHARED_SECRET=your_shared_secret
IXOPAY_LANGUAGE=en
IXOPAY_GATEWAY_URL=https://gateway.ixopay.com/
```

#### 4. Resolve the client from the container

```php
use Ixopay\Client\Client;

Route::post('/checkout/ixopay/debit', function (Client $ixopay) {
    // Build a Debit transaction and send it using the injected SDK client.
});
```

#### 5. Use the provided Laravel examples

The repository now includes framework-oriented examples under
[`examples/laravel`](examples/laravel):

- `CheckoutController.php` for a debit / redirect flow
- `CallbackController.php` for signed callback handling through a dedicated helper
- `routes.php` for a minimal route setup

These files are meant as integration starters you can copy into a Laravel app.

#### 6. Laravel features included

- service container binding for `Ixopay\Client\Client`
- `Ixopay` facade alias
- publishable config file
- `ixopay:install` artisan command
- `CallbackRequest` helper for validating and parsing signed callbacks
- Testbench coverage for provider registration and config-driven client setup

## Application Notes

This fork also carries a small product and developer-experience proposal pack
that builds on IXOPAY's public documentation and the Laravel integration work
implemented here.

- [Application overview](docs/application/README.md)
- [Suggested updates vs new features](docs/application/suggested-updates-vs-new-features.md)
- [Payment UI Lab](docs/application/payment-ui-lab.md)
- [Receipt Lab](docs/application/receipt-lab.md)
- [Sandbox workflow concept](docs/application/sandbox-workflow-concept.md)
- [Developer assistant concept](docs/application/developer-assistant-concept.md)

## Support

If you have suggestions for new features, spotted a bug, or encountered a
technical problem, [create an issue here][repo-new-issue].
Also, you can always contact IXOPAY's Support Team as defined in your contract.

## Licence

This repository is available under the [MIT License][license].

## See also

- [Documentation][ixopay-docs-gateway]
- [API Reference][ixopay-docs-api]

<!-- references -->
[license]: LICENSE.md
[ixopay]: https://ixopay.com
[ixopay-docs-api]: https://documentation.ixopay.com/api/transaction/transaction-api
[ixopay-docs-gateway]: https://documentation.ixopay.com
[repo-new-issue]: https://github.com/ixopay/php-ixopay/issues/new/choose
[packagist-shield]: https://img.shields.io/packagist/v/ixopay/ixopay-php-client.svg
[packagist-url]: https://packagist.org/packages/ixopay/ixopay-php-client
[php-shield]: https://img.shields.io/packagist/php-v/ixopay/ixopay-php-client.svg
[license-shield]: https://img.shields.io/github/license/ixopay/php-ixopay.svg
[composer]: https://getcomposer.org
