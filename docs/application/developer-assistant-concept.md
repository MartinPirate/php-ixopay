# Developer Assistant Concept

This is a new feature proposal: an assistant grounded in IXOPAY's own
documentation and adapter capabilities.

## Public anchors

- Integration overview:
  https://documentation.ixopay.com/docs/reference/integration
- Processing options:
  https://documentation.ixopay.com/docs/reference/integration/processing-options
- `payment.js`:
  https://documentation.ixopay.com/docs/reference/integration/payment.js/
- Hosted payment pages:
  https://documentation.ixopay.com/docs/reference/integration/processing-options/hosted-payment-pages
- Recipes:
  https://documentation.ixopay.com/docs/recipes

## Proposal

Build an assistant that answers implementation questions like:

- "We need Kenya mobile money plus card fallback."
- "Should we use hosted payment pages or payment.js for this merchant?"
- "What do we need to test for callbacks and 3-D Secure?"

## Expected output

The assistant should return:

- recommended processing option
- relevant docs links
- likely adapter or payment-method candidates
- integration steps
- callback expectations
- sandbox test flow

## Why this matters

The value is not generic chat. The value is compressing IXOPAY-specific
integration knowledge into actionable guidance for merchants, partners, and
solutions engineers.
