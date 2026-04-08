# IXOPAY Application Notes

This fork combines two things:

- implemented SDK work:
  - Laravel integration support in this repository
- proposal work:
  - product and developer-experience concepts grounded in IXOPAY's public docs

## Why this lives in the fork

The goal is simple: if someone opens the fork, they should be able to see both
real implementation work and the product thinking that motivated it.

## What is implemented in this fork

- Laravel service provider and client manager
- publishable config and `Ixopay` facade
- `ixopay:install` artisan command
- callback helper for signed callback validation
- Laravel examples
- PHPUnit + Testbench coverage
- PHPStan support

## What is proposed in this docs pack

- [Suggested updates vs new features](suggested-updates-vs-new-features.md)
- [Payment UI Lab](payment-ui-lab.md)
- [Receipt Lab](receipt-lab.md)
- [Sandbox workflow concept](sandbox-workflow-concept.md)
- [Developer assistant concept](developer-assistant-concept.md)

## Public documentation references

These concept notes are anchored to IXOPAY's public docs rather than guesses:

- Hosted payment pages:
  https://documentation.ixopay.com/docs/reference/integration/processing-options/hosted-payment-pages
- Hosted payment pages guide:
  https://documentation.ixopay.com/docs/guides/getting-started/accept-payments/hosted-payment-pages
- Hosted fields / `payment.js`:
  https://documentation.ixopay.com/docs/reference/integration/payment.js/
- Payment method selection:
  https://documentation.ixopay.com/docs/reference/features/payment-method-selection
- Multi-Method Connector:
  https://documentation.ixopay.com/manual/docs/connector/multi-method-connector
- FAST Editor:
  https://documentation.ixopay.com/manual/docs/fast
- Pay-by-Link:
  https://documentation.ixopay.com/docs/guides/features/pay-by-link
- Testing your setup:
  https://documentation.ixopay.com/docs/guides/getting-started/testing
- Testing 3-D Secure:
  https://documentation.ixopay.com/docs/reference/features/3d-secure/testing

## Positioning

The important distinction is:

- if IXOPAY already documents it publicly, I treat it as a suggested update
- if I could not clearly verify it publicly, I treat it as a new feature
- if public evidence is partial, I frame it as expansion
