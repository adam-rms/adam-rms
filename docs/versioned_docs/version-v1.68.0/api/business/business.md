---
id: business
title: Business
sidebar_position: 1
---

# Businesses 

:::caution This documentation is a work in progress.

This file was written by volunteers and then programmatically organized into this site, so there may be errors and typos. Hit "Edit this Page" at the bottom to correct them.

:::

## addUser

Add existing user to instance
```
instances/addUser.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
users
rolegroup
rolename
```

## addUserFromCode

User self signup to join instance
```
instances/addUserFromCode.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
signupCodes_name
```

## archiveUser

Archive user from instance
```
instances/archiveUser.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
userid
```

## editInstance

Update instance data
```
instances/editInstance.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
formData
```

## editInstancePublicSite

Update instance's public data
```
instances/editInstancePublicSite.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
formData
```

## editUser

Update user position and label within instance
```
instances/editUser.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
userinstanceid
position
label
```

## list

Get all instances
```
instances/list.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
None
```

## new

Create new instance
```
instances/new.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
instances_name
instance_website
instance_email
instance_phone
role
```

## removeUser

Remove user from instance
```
instances/removeUser.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
userid
```

## searchUser

Find specific user
```
instances/searchUser.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
term
```

## delete

Remove an asset status
```
instances/assetAssignmentStatus/delete.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
statusId
```

## edit

Edit name of asset status
```
instances/assetAssignmentStatus/edit.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
statusName
statusId
```

## new

Add new asset status
```
instances/assetAssignmentStatus/new.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
statusName
statusOrder
```

## reorder

Update order of asset statuses
```
instances/assetAssignmentStatus/reorder.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
order
```

## edit

Update project type
```
instances/projectTypes/edit.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
formData
```

## new

Create new project type
```
instances/projectTypes/new.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
formData
```

## edit

Update signup code
```
instances/signupCode/edit.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
formData
```

## new

Create signup code
```
instances/signupCode/new.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
formData
```

## taken


```
instances/signupCode/taken.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
[GET] signupCode
```

