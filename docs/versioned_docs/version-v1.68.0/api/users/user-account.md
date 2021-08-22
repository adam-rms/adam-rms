---
id: user-account
title: User Account
sidebar_label: Account
---

# User Account 

:::caution This documentation is a work in progress.

This file was written by volunteers and then programmatically organized into this site, so there may be errors and typos. Hit "Edit this Page" at the bottom to correct them.

:::

## basicDetails

account/basicDetails.php
```
 includes 'users_userid' to identify the user od"
```

 **Parameters**

Parameters are POST unless otherwise noted

```
"formData: data from account information form in 'name' => 'value' pairs
```

## calendar-export

Endpoint for external calendar requests
```
account/calendar-export.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
Lots
```

## changePass

Update a user's password
```
account/changePass.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
oldpass
newpass
```

## destroyTokens

Invalidates user access tokens
```
account/destroyTokens.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
userid
```

## disconnectOAuth

Removes 3rd party auth solutions
```
account/disconnectOAuth.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
users_userid
provider
```

## emailViewer

Displays emails captured for auditing
```
account/emailViewer.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
[GET] CSV of email ids
```

## forcePasswordChange

account/forcePasswordChange.php
```
 handled through PAGEDATA"
```

 **Parameters**

Parameters are POST unless otherwise noted

```
"None
```

## linkOAuth

account/linkOAuth.php
```
 currently just 'google'"
```

 **Parameters**

Parameters are POST unless otherwise noted

```
"[GET] auth provider name
```

## notifications

Update user's notification options
```
account/notifications.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
users_userid
settings
```

## passwordReset

Use sent code to reset user password (i think)
```
account/passwordReset.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
code
```

## permissions


```
account/permissions.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```

```

## suspend

Suspend a user's account
```
account/suspend.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
userid
```

## theme

account/theme.php
```
 handled through PAGEDATA"
```

 **Parameters**

Parameters are POST unless otherwise noted

```
"None
```

## thumbnail

account/thumbnail.php
```
 can be fetched from PAGEDATA&&&thumbnail: adam-rms url to image file"
```

 **Parameters**

Parameters are POST unless otherwise noted

```
"users_userid: Optional
```

## verifyEmail

Complete verification of user's email address
```
account/verifyEmail.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
code: auth code from email
```

## viewSiteAs

View rms from specified user
```
account/viewSiteAs.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
userid: id of user to view
```

## ViewSiteAs_terminate

account/ViewSiteAs_terminate.php
```
 handled through PAGEDATA"
```

 **Parameters**

Parameters are POST unless otherwise noted

```
"None
```

## widgetToggle

Display or remove a widget from user's dashboard
```
account/widgetToggle.php
```

 **Parameters**

Parameters are POST unless otherwise noted

```
widgetName
```

