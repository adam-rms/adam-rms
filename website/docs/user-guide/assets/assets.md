---
sidebar_position: 11
title: Assets
---

# Assets

Assets are the core of AdamRMS. They represent the equipment your business owns, hires out, or manages -- from lighting rigs and sound desks to cables and flight cases.

## Key Concepts

AdamRMS uses a two-level system for tracking equipment:

- **Asset Types** describe a *kind* of equipment (e.g. "ETC Source Four Jr 25-50°"). An asset type holds shared information like the manufacturer, hire rates, value, category, description, and custom field definitions.
- **Individual Assets** are the actual physical items you own. Each individual asset belongs to an asset type and has its own unique **asset tag** (e.g. `A-0042`), along with per-item data like notes, custom field values, storage location, and barcodes.

For example, you might have an asset type called "5m XLR Cable" with 30 individual assets, each with a unique tag and potentially different custom field values (e.g. cable colour).

:::tip
Think of asset types like a product in a catalogue, and individual assets like the specific units on your shelves.
:::

## Asset Types

Each asset type includes:

| Field | Description |
|-------|-------------|
| **Name** | The name of the asset type |
| **Manufacturer** | The equipment manufacturer |
| **Category** | A category and category group for organising assets |
| **Day Rate** | The daily hire rate |
| **Week Rate** | The weekly hire rate |
| **Value** | The replacement value of the asset |
| **Mass** | The weight of the asset (useful for transport planning) |
| **Description** | A free-text description |
| **Product Link** | A URL to the manufacturer's product page |
| **Custom Fields** | Up to 10 definable fields per asset type (e.g. "Length", "Colour", "Serial Number") |
| **Thumbnails** | Images of the asset type |
| **Files** | Attached documents such as user manuals or spec sheets |

Asset types can optionally be shared across businesses (instances) in your AdamRMS deployment, or be specific to a single business.

## Individual Assets

Each individual asset has:

- **Asset Tag** -- a unique identifier, either auto-generated (format `A-XXXX`) or manually entered
- **Notes** -- free-text notes (e.g. purchase date, condition)
- **Custom Field Values** -- values for the fields defined on the asset type
- **Storage Location** -- where the asset is currently stored
- **Asset Groups** -- groups the asset belongs to (see [Asset Groups](./asset-groups))
- **Barcodes** -- physical barcodes attached to the asset (see [Asset Barcodes](./asset-barcodes))
- **Files** -- per-asset documents (e.g. purchase invoices)
- **Linked Assets** -- assets that should be assigned together (e.g. a lamp linked to its case)

### Asset Lifecycle

Individual assets have a lifecycle controlled by their **end date**:

- **Active assets** have no end date, or an end date in the future. They appear in normal searches and can be assigned to projects.
- **Archived assets** have an end date in the past. They are hidden from searches by default but can be revealed using the "Show Archived" option.

This is useful for tracking when equipment is retired, sold, or otherwise taken out of service without permanently deleting the record.

### Flags and Blocks

Assets can have **flags** and **blocks** that affect their availability:

- **Flags** are informational warnings (e.g. "requires PAT testing") that appear on the asset but don't prevent assignment.
- **Blocks** prevent an asset from being assigned to projects. These are typically set by [maintenance jobs](./maintenance).

## Asset Categories

Assets are organised into **categories**, which are themselves grouped into **category groups**. For example:

- **Category Group**: Lighting
  - **Category**: Conventionals
  - **Category**: Moving Lights
  - **Category**: LED Fixtures

Categories help you filter and find assets quickly in the [asset search](./finding-assets).

:::note Permissions Required
ASSETS:CREATE
ASSETS:ASSET_TYPES:CREATE
ASSETS:ASSET_TYPES:EDIT
:::

## What's Next?

- [Creating New Assets](./new-assets) -- how to add asset types and individual assets
- [Finding Assets](./finding-assets) -- searching, filtering, and browsing your asset catalogue
- [Asset Groups](./asset-groups) -- organising assets into custom groups
- [Asset Barcodes](./asset-barcodes) -- barcode scanning and tracking
- [Maintenance](./maintenance) -- managing maintenance jobs for your assets
