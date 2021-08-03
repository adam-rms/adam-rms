---
id: assets
title: Assets
sidebar_position: 1
---

# Assets 

:::caution This documentation is a work in progress.

This file was written by volunteers and then programmatically organized into this site, so there may be errors and typos. Hit "Edit this Page" at the bottom to correct them.

:::

## archive

Archive an asset
```
assets/archive.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
assets_id
date
```

## deepSearch

Search for assets thoroughly
```
assets/deepSearch.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
projectid
public
dates
instance_id
page
page_limit
sort
category
keyword
manufacturer
group
showlinked
showarchived
tags
html
```

## delete

"Safely delete an asset. removes linked assets
```
assets/delete.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
assets_id
```

## editAsset

assets/editAsset.php
```
 although can be sent as individual key values"
```

 **Parameters**

Parameters are POST unless otherwise noted

```
"formData: Predominantly handled through this
```

## editAssetType

"Edit a general asset type
```
assets/editAssetType.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
formData
```

## editAssetTypeDefinableFields

Update an asset's definable fields values
```
assets/editAssetTypeDefinableFields.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
formData: Contents of definable fields values
```

## export

Export all assets as csv or xlsx
```
assets/export.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
csv OR xlcx: format to export as
```

## getAssetTypeData

Get all information for asset type
```
assets/getAssetTypeData.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
term
```

## list

List assets based on given terms
```
assets/list.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
term
page
category
manufacturer
assetTypes_id
imageComp
all
abridgedList
```

## newAssetFromType

Create asset based on an asset type
```
assets/newAssetFromType.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
formData
```

## newAssetType

Create new asset type
```
assets/newAssetType.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
formData
```

## searchAssets

Search for a single asset simply
```
assets/searchAssets.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
term
```

## searchAssetsBarcode

[redirect] assets/barcodes/search
```
assets/searchAssetsBarcode.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
none - redirects to another api
```

## searchType

Search for an asset type
```
assets/searchType.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
manufacturer
term
```

