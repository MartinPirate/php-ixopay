# Sandbox Workflow Concept

IXOPAY already appears to have strong testing and simulation capabilities
publicly. The improvement opportunity is workflow quality.

## Public anchors

- Testing your setup:
  https://documentation.ixopay.com/docs/guides/getting-started/testing
- Testing 3-D Secure:
  https://documentation.ixopay.com/docs/reference/features/3d-secure/testing

## Proposal

Turn the sandbox into a guided launch workflow.

## What that means

### Guided first-payment setup

- one path for credentials, headers, callback URL, and sample requests
- quicker time-to-first-success for evaluation teams

### Scenario simulation

- issuer decline
- 3-D Secure timeout
- webhook retry
- redirect cancelled by customer
- fallback PSP handoff

### Callback and replay tooling

- replay callbacks against merchant endpoints
- expose signature and payload diagnostics
- shorten integration debugging loops

### Method-specific testing kits

- curated presets for hosted payment pages
- `payment.js` starter flow
- regional method test recipes

## Why it matters

IXOPAY's public documentation already supports testing. A better sandbox
workflow would reduce friction during evaluations, partner onboarding, and
merchant proof-of-concept work.
