---
sidebar_position: 12
title: Finding Assets
---

# Finding assets

AdamRMS uses a comprehensive search system to help you find assets. You can open the search engine by clicking on the warehouse button on the left menu bar.

You can search by:

- Keyword - Name of asset (eg. Source 4 Jr)
- Tags - Asset ID (A-XXXX) or custom barcode value (eg T-3214)
- Categories - eg. ‘Lighting - Conventionals’
- Manufacturers - eg. ‘ETC’
- Groups - Business asset groups
- Business - you can only select one at once

![The Asset Search page](/img/tutorial/assets/assets-search.png)

Asset availability can be checked by selecting a project (or a range of dates if you do not have permission to assign assets to projects)

Page Limit, Sorting, Linked Assets and Archived assets are additional options if you need further control over searching.  
From the search page, you can also export a full list of assets in either .xlsx or .csv formats.

## Listing Assets

---

Once you’ve searched for some assets, they’ll be listed in pages.  
Each asset type has an add-to-project option, which allows you to add any assets that can be added to the project.  
You can also view more information about an asset by clicking on it!

## Viewing Assets

---

Asset pages initially show the general Asset Type, which contains information about the asset as a whole. This includes a calendar that shows all asset assignments, and a list of all assets of that asset type

![Asset Type Listing of XLR](/img/tutorial/assets/assets-xlr-listing.png "XLR asset type in Demo Hire services")
_Example Asset Type page_

## Asset Type Files

---

Asset types can have files stored with them.

:::note Permissions Required
ASSETS:ASSET_TYPE_FILE_ATTACHMENTS:VIEW  
ASSETS:ASSET_TYPE_FILE_ATTACHMENTS:CREATE  
:::

For asset types it is suggested the files are for user manuals, info sheets and more general documentation.  
More information about files on AdamRMS can be found [here](./../../hosting/intro#file-storage)

### Editing asset types

---

Asset types can be altered to add additional information, Custom fields and asset thumbnails.

:::note Permissions Required
ASSETS:ASSET_TYPES:CREATE  
ASSETS:ASSET_TYPES:EDIT  
:::

![Editing asset Type Listing of XLR](/img/tutorial/assets/assets-xlr-edit.png "Editing XLR asset type in Demo Hire services")
_Editing XLR asset type_

Custom Fields are additional information about an asset that can be defined on an asset type basis. For example, you may use them to record the lengths of different XLR cables. To do this, the XLR asset type should have a Custom Field called Length, which can be assigned for each asset.  
All asset types can have multiple thumbnails which can be uploaded in this section.

:::caution
If you are unable to edit an asset, it may be a built-in asset. Contact AdamRMS support to make changes to these assets.
:::

### Individual Assets

---

From the assets list, an individual asset can be viewed. The individual asset holds more information about the single object.

![Editing an individual asset](/img/tutorial/assets/assets-xlr-listing-individual.png "Editing an individual XLR asset in Demo Hire services")
_Editing an individual XLR asset_

- You can assign assets to a [group](./asset-groups) from the group tab.
- Individual assets can have associated files, usually used for invoices.
- Assets can be assigned barcodes
- [Asset Maintenance](./maintenance) can be managed in the individual asset page, although the maintenance pages are more comprehensive.

#### Asset Linking

Assets can be linked to other assets, so that those assets are assigned to a project when the main asset is assigned.  
To link an asset, search for the parent asset in the asset link box of the child asset.
