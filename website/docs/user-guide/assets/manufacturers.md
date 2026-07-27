---
sidebar_position: 17
title: Manufacturers
---

# Manufacturers

Manufacturers represent the companies that produce your equipment. Every asset type in AdamRMS is linked to a manufacturer, making it easy to browse your catalogue by brand and find manufacturer information.

:::note Permissions Required
ASSETS:MANUFACTURERS:CREATE  
ASSETS:MANUFACTURERS:EDIT  
:::

## Viewing Manufacturers

The manufacturers page shows all manufacturers that have assets in your business, plus any manufacturers you've created specifically for your business. Each manufacturer entry displays:

- **Name** -- the manufacturer's name
- **Website** -- a link to the manufacturer's website (opens in a new tab)
- **Notes** -- any additional notes about the manufacturer

You can click the **eye** icon next to a manufacturer to view all of their assets in the [asset search](./finding-assets), which automatically filters by that manufacturer and includes both linked and archived assets.

### Searching

Use the search box to filter manufacturers by name or website URL. This is useful when you have a large number of manufacturers.

## Creating a Manufacturer

1. Click the **+** button in the top-right of the manufacturers page.
2. Enter the manufacturer's name in the prompt.
3. The manufacturer is created and the page refreshes.

You can then edit the manufacturer to add a website and notes.

:::tip
Manufacturers are also created automatically when you add a new asset type -- you can type a new manufacturer name during asset creation and it will be created for you. The manufacturers page is useful for managing and enriching these entries afterwards.
:::

## Editing a Manufacturer

1. Click the **edit** icon next to a manufacturer.
2. Update any of the following fields:
   - **Name** (required)
   - **Website**
   - **Notes** (multi-line)
3. Click **Save changes**.

:::caution
You can only edit manufacturers that belong to your business. Some manufacturers may be shared across all businesses on the platform (built-in manufacturers) and cannot be edited.
:::

## Shared vs Business-Specific Manufacturers

AdamRMS has two types of manufacturers:

- **Shared manufacturers** are available to all businesses on the platform. These typically represent well-known equipment brands (e.g. ETC, Shure, Sennheiser). They cannot be edited by individual businesses.
- **Business-specific manufacturers** are created by your business and are only visible to you. These can be freely edited.

Both types appear in the manufacturer list and can be assigned to asset types.

## Related Features

- [Assets](./assets) -- asset types are linked to manufacturers
- [New Assets](./new-assets) -- selecting a manufacturer when creating asset types
- [Finding Assets](./finding-assets) -- filtering assets by manufacturer
