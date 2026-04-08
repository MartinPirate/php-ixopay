# Receipt Lab

The strongest genuinely new feature candidate is a receipt system that sits on
top of IXOPAY's existing template capabilities.

## Public anchors

- FAST Editor:
  https://documentation.ixopay.com/manual/docs/fast
- Pay-by-Link:
  https://documentation.ixopay.com/docs/guides/features/pay-by-link

These public docs show strong evidence for payment templates, email templates,
and result templates. They do not clearly show a structured receipt design
system or a receipt gallery.

## Proposal

Introduce a receipt system with:

- customer receipt templates
- merchant settlement summary templates
- success, pending, failed, and cancelled result-state templates
- confirmation email variants that match the receipt style

## Template packs

### Travel

- passenger and itinerary details
- booking reference and service summary
- refund/cancellation block

### Retail

- SKU lines and tax lines
- pickup or shipping summary
- refund policy block

### Subscription

- billing cycle context
- mandate or token reference
- renewal confirmation structure

### Marketplace

- seller or split-payout references
- order-part breakdown
- support-routing details

## Why it matters

Receipts are one of the few merchant-customer payment surfaces that persist
after checkout. Improving them creates visible value even when IXOPAY is not
the brand in front of the customer during the whole purchase flow.
