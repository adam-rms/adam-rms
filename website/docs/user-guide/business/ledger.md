---
sidebar_position: 39
title: Ledger
---

# Ledger

The Ledger provides a centralised view of all payment records across your business. It shows every payment that has been received against any project, giving you a single place to review your business's incoming finances.

:::note Permissions Required
FINANCE:PAYMENTS_LEDGER:VIEW  
:::

## Viewing the Ledger

The ledger displays payments in a table, sorted by date (most recent first), with the following columns:

| Column | Description |
|--------|-------------|
| **Reference** | The payment reference (e.g. invoice number, bank reference) |
| **Date** | When the payment was received |
| **From** | The client who made the payment (links to the client search) |
| **Method** | The payment method (e.g. bank transfer, card, cash) |
| **Comment** | Any notes or comments about the payment |
| **Amount** | The payment amount in your business's configured currency |
| **Project** | The project the payment is associated with (links to the project page) |

### Searching

Use the search box in the top-right corner to filter payments by reference or amount. This is useful for finding a specific transaction.

### Pagination

Payments are displayed 20 per page. Use the pagination controls at the bottom of the table to navigate between pages.

## Payment File Attachments

If file storage is enabled for your business, each payment can have files attached to it (e.g. remittance advice, bank statements, receipts).

:::note Permissions Required
PROJECTS:PROJECT_PAYMENTS:VIEW:FILE_ATTACHMENTS  
PROJECTS:PROJECT_PAYMENTS:CREATE:FILE_ATTACHMENTS  
:::

Click the **paperclip** icon next to a payment to view or upload file attachments. The icon shows a count of existing attachments.

## How Payments Are Added

Payments are not created directly from the ledger. Instead, they are added from within individual projects via the [Project Finance](../projects/finance) page. The ledger is a read-only view that aggregates all payments across projects.

:::tip
To add a new payment, navigate to the relevant project's finance section and record the payment there. It will then appear automatically in the ledger.
:::

## Related Features

- [Project Finance](../projects/finance) -- managing invoices, quotes, and recording payments within projects
- [Clients](./clients) -- viewing financial totals per client
- [Business Utilities](./business-utilities) -- other business-wide financial tools
