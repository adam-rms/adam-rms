---
sidebar_position: 37
title: Clients
---

# Clients

The Clients page lets you manage your business's client directory. Clients are the organisations or individuals you hire equipment to, and they can be linked to projects to track financial relationships.

:::note Permissions Required
CLIENTS:VIEW  
CLIENTS:CREATE  
CLIENTS:EDIT  
:::

## Viewing Clients

The client list is displayed as a table with the following columns:

| Column | Description |
|--------|-------------|
| **Name** | The client's name (links to their projects if you have project view permission) |
| **Address** | A map icon links to Google Maps if an address is provided |
| **Phone** | Contact phone number |
| **Email** | Contact email address |
| **Website** | Client website (opens in a new tab) |
| **Notes** | Any additional notes about the client |
| **Total Paid** | Total payments received from this client |
| **To Pay** | Outstanding balance across their projects |

### Filtering Clients

The client list provides several filtering options:

- **Search** -- type in the search box to filter by name, address, email, website, phone, or notes
- **Include future projects** -- by default, only past projects are counted for financial totals. Toggle this to include upcoming projects.
- **Include released projects** -- include projects where assets have been released in the outstanding balance calculation
- **Show archived** -- switch between active and archived clients

## Creating a Client

1. Click the **+** button in the top-right of the client list.
2. Enter the client's name in the prompt.
3. The client is created and the page refreshes.

You can then edit the client to add further details.

## Editing a Client

1. Click the **edit** icon next to a client to open the edit modal.
2. Update any of the following fields:
   - **Name**
   - **Address** (multi-line)
   - **Website**
   - **Phone**
   - **Email**
   - **Notes** (multi-line)
3. Click **Save changes**.

## Archiving and Restoring Clients

- To archive a client, click the **archive** icon (amber) next to their name. Archived clients won't appear in the active list and can't be assigned to new projects.
- To restore an archived client, switch to the archived view and click the **restore** icon (green).

:::tip
Archiving a client does not delete them or affect existing projects. It simply hides them from the default view and prevents new project assignments.
:::

## Financial Tracking

The client list provides a quick financial overview for each client:

- **Total Paid** shows the sum of all payments received across their projects (only projects with finance enabled via their project type).
- **To Pay** shows the outstanding balance. By default, this excludes projects where assets have been released and future projects, but you can toggle these filters.

For detailed financial information about a specific client's projects, click on the client's name to view their project list.

## Related Features

- [Projects](../projects/projects) -- creating and managing projects linked to clients
- [Project Finance](../projects/finance) -- managing invoices, quotes, and payments
- [Ledger](../business/ledger) -- viewing all payments across the business
- [Locations](../business/locations) -- managing venues (which can also be linked to clients)
