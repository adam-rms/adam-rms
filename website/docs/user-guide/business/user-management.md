---
sidebar_position: 32
title: User Management
---

# User Management

## Users
---
The Users page lists all users associated with a business.

:::note Permissions Required
BUSINESS:USERS:VIEW:LIST  
BUSINESS:USERS:CREATE:ADD_USER_BY_EMAIL  
BUSINESS:USERS:DELETE:REMOVE_FORM_BUSINESS  
BUSINESS:USERS:EDIT:CHANGE_ROLE  
BUSINESS:USERS:EDIT:USER_THUMBNAIL  
BUSINESS:USERS:VIEW:INDIVIDUAL_USER  
BUSINESS:USERS:EDIT:ARCHIVE  
:::  

![Users list](/img/tutorial/businesses/user-users.png)
*List of users*

To add a user to a business, you can either add them by email (once they have created an account) or use a [signup code](#signup-codes).  
Each user has a role group within the business that is defined by what [permissions](#permissions) they have. They can also have a Role in the business, which is a text entry that can have any value.  

## Permissions
---
AdamRMS has a granular permission system which lets you define exactly what a user can do.

:::note Permissions Required
BUSINESS:ROLES_AND_PERMISSIONS:VIEW  
BUSINESS:ROLES_AND_PERMISSIONS:EDIT  
BUSINESS:USERS:EDIT:ROLES_AND_PERMISSIONS  
BUSINESS:ROLES_AND_PERMISSIONS:CREATE  
:::

![Permission List](/img/tutorial/businesses/user-permissions.png)
*AdamRMS Permissions*

Throughout this guide permissions have been included in each section, but they are all listed on the permissions page.

Permissions are assigned to a Role Group, which can be created on a per-business basis. By default, businesses have an ‘Administrator’ Role group that has access to all permissions.


## Signup Codes
---
Signup codes are a way for individuals to automatically be added to your business as a specific role Group.

:::note Permissions Required
BUSINESS:USER_SIGNUP_CODES:VIEW  
BUSINESS:USER_SIGNUP_CODES:CREATE  
BUSINESS:USER_SIGNUP_CODES:EDIT  
BUSINESS:USER_SIGNUP_CODES:DELETE  
:::

![Signup Codes](/img/tutorial/businesses/user-signup.png)
*Business signup codes*

The code is the item users enter to get access to your business.  
AdamRMS tracks how many uses a code has had, and you can receive an email every time a signup code is used. 

## Trusted Domains
---
Trusted Domains allows users on AdamRMS with verified email addresses to join your businesses if their email matches a domain you enter below.

They are similar to Signup Codes, in that they allow others to join your business, but they are automatic and are offered to any user in any domain you list.

:::note Permissions Required
BUSINESS:SETTINGS:EDIT:TRUSTED_DOMAINS  
:::

![Trusted Domains](/img/tutorial/businesses/user-trusted-domains.png)