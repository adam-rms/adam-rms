---
id: cms-pages
title: CMS Pages
---

# CMS Pages 

:::caution This documentation is a work in progress.

This file was written by volunteers and then programmatically organized into this site, so there may be errors and typos. Hit "Edit this Page" at the bottom to correct them.

:::

## editPageConfig

Edit information about a cms page
```
cms/editPageConfig.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
formData
```

## editPageContent

Edit content of a cms page
```
cms/editPageContent.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
cmsPages_id
pageData
changelog
```

## editPageContent-rollback

Reverse an edit of a cms page
```
cms/editPageContent-rollback.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
cmsPages_id
change
```

## editPageRank

Update order of cms pages
```
cms/editPageRank.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
order
```

## get

Display a page using cmspage.twig template
```
cms/get.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
p (page)
```

## list

List all cms pages
```
cms/list.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
none
```

