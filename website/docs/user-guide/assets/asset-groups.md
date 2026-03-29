---
sidebar_position: 14
title: Asset Groups
---

# Asset Groups

Asset groups allow you to organise sets of assets under a named label. This is useful for tracking collections of equipment that are frequently used together, managing assets by purchase batch, or creating personal shortlists.

![Assets Group Page](/img/tutorial/assets/assets-groups.png "XLR asset group in Demo Hire Services")
*Group Listing*

:::note Permissions Required
ASSETS:ASSET_GROUPS:CREATE
ASSETS:ASSET_GROUPS:EDIT
ASSETS:ASSET_GROUPS:DELETE:ASSETS_WITHIN_GROUP
ASSETS:ASSET_GROUPS:EDIT:ASSETS_WITHIN_GROUP
:::

## Creating a Group

1. In the side menu, under **ASSETS**, click **Groups** to open the Asset Groups page.
2. Click the **+** button.
3. Enter a group name and description.
4. Optionally tick **Visible to only me** to make the group personal (unticked groups are visible to all users in the business).

## Adding Assets to a Group

Assets can be added to groups in two ways:

- **From the groups page** -- select assets to add to an existing group.
- **From an individual asset's page** -- use the edit form on a specific asset to assign it to one or more groups.

An asset can belong to multiple groups simultaneously.

## Watching Groups

You can **watch** a group to receive email notifications whenever:

- An asset is added to or removed from the group
- An asset in the group is assigned to or removed from a project

This is useful for keeping track of equipment that you're responsible for, without needing to manually check for changes.

:::tip
Notification preferences for group watching can be customised from your [account page](../account#notifications). You can enable or disable individual notification types for watched groups.
:::

## Assigning a Group to a Project

If you have permission to assign assets to projects (`PROJECTS:PROJECT_ASSETS:CREATE:ASSIGN_AND_UNASSIGN`), you can assign **all assets in a group** to a project in one step. This is much faster than adding assets individually when you have a standard kit list.

## Filtering by Group

Groups can be used as a filter in the [asset search](./finding-assets). Select one or more groups in the search filters to show only assets belonging to those groups.

## Use Cases

- **Kit lists** -- create groups for standard event setups (e.g. "Festival PA Rig", "Conference AV Pack") so you can assign them to projects quickly.
- **Purchase batches** -- group assets by purchase date to track warranty periods or depreciation.
- **Personal tracking** -- create private groups to keep a shortlist of assets you're responsible for or working with.
- **Cable runs** -- group cables and connectors for specific venue setups.

## Related Features

- [Assets](./assets) -- understanding asset types and individual assets
- [Finding Assets](./finding-assets) -- filtering assets by group in search
- [Asset Barcodes](./asset-barcodes) -- generating barcode sheets by group
- [Project Assets](../projects/assets) -- assigning assets to projects
