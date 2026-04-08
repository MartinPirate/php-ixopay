# Payment UI Lab

IXOPAY already documents hosted payment pages, payment selection, and
`payment.js`. That makes checkout UI a strong suggested-update lane.

## Public anchors

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

## Proposal

Create a premium default UI kit for:

- hosted payment pages
- payment selection pages
- hosted-fields checkout surfaces built with `payment.js`
- result pages that visually match the original checkout

## What should improve

### Animated method cards

- stronger default method hierarchy
- device-aware layout for mobile vs desktop
- motion that signals selection and trust instead of decorative noise

### Better trust cues

- clearer state before redirect
- clearer 3-D Secure explanation and fallback messaging
- better pending-state communication for non-instant methods

### Regional method ordering

- merchant preset by country or corridor
- mobile money and wallet-first ordering on mobile devices
- smoother transition from selection screen to receipt/result screens

## Why it matters

Hosted payment flows are often the fastest path to value for merchants with
limited front-end resources. If IXOPAY owns that surface, the default visual
quality should feel premium instead of merely functional.
