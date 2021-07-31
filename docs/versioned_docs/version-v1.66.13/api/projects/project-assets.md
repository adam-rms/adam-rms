---
id: project-assets
title: Project Assets
sidebar_label: Assets
---


# Project Assets

:::caution This documentation is a work in progress.

This file was written by volunteers and then programmatically organized into this site, so there may be errors and typos. Hit "Edit this Page" at the bottom to correct them.

:::


## assign.php

Add asset to project
```
projects/assets/assign.php.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
projects_id
assetGroups_id
assets_id
```

## setComment.php

Update asset assignment comment
```
projects/assets/setComment.php.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
assetsAssignments
assetsAssignments_comment
```

## setDiscount.php

Update asset assignment discount
```
projects/assets/setDiscount.php.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
assetsAssignments
assetsAssignments_discount
```

## setPrice.php

"Update asset assignment custom price
```
projects/assets/setPrice.php.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
assetsAssignments
assetsAssignments_customPrice
```

## setStatus.php

Update asset assignment status
```
projects/assets/setStatus.php.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
assetsAssignments_id
assetsAssignments_status
status_is_order
```

## setStatusBarcode.php

Handle asset status scanning
```
projects/assets/setStatusBarcode.php.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
projects_id
assetsAssignments_status
text
type
locationType
location
```

## unassign.php

Remove asset from project and update finance cache
```
projects/assets/unassign.php.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
assetsAssignments
assets_id
```

