---
id: barcodes
title: Asset Barcodes
sidebar_label: Barcodes
---

# Barcodes 

:::caution This documentation is a work in progress.

This file was written by volunteers and then programmatically organized into this site, so there may be errors and typos. Hit "Edit this Page" at the bottom to correct them.

:::

## assign

Assign a barcode to an asset
```
assets/barcodes/assign.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
tag
barcodeid
text
```

## delete

Delete a barcode
```
assets/barcodes/delete.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
barcodes_id
```

## index

Handles barcode scanning
```
assets/barcodes/index.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
size
width
type
barcode
```

## search

Find an asset by barcode
```
assets/barcodes/search.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
text
locationType
location
```

## searchAsset

Find an asset by barcode
```
barcodes/searchAsset.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
term
```

