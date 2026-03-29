---
sidebar_position: 33
title: "Permissions Reference"
---

# Permissions Reference

AdamRMS uses a **two-tier permission system** to control what users can do across the platform. Understanding this system is key to managing your team effectively and keeping your data secure.

- **Instance permissions** (also called "business permissions") control what a user can do _within a specific business_. These cover day-to-day actions such as managing assets, projects, locations, and users within your business.
- **Server permissions** control what a user can do at the _platform level_. These are reserved for system administrators and cover actions like managing all users across the server, administering instances, and editing global configuration.

Most users will only ever interact with instance permissions. Server permissions are typically only relevant if you are self-hosting AdamRMS or are a platform administrator.

:::tip
If you are looking for information on how to create Role Groups and assign permissions to users, see the [User Management](./user-management.md) page.
:::

## How permissions are assigned

Permissions are assigned to users via **Role Groups**. Each business can create its own Role Groups, and each Role Group has a set of permissions associated with it. When you add a user to a business, you assign them a Role Group, which determines what they can do.

By default, every business has an "Administrator" Role Group that grants access to all instance permissions. You can create additional Role Groups with more limited access to suit different roles in your organisation.

:::note
A user's Role Group is set per business. If a user belongs to multiple businesses, they may have different Role Groups (and therefore different permissions) in each one.
:::

## Token types

Some permissions support different **token types**, which control how the permission can be exercised:

| Token Type | Description |
|---|---|
| `web-session` | The standard web browser session. Almost all permissions support this. |
| `app-v1` | The legacy mobile app (v1). Only some permissions are available through this token type. |
| `app-v2-magic-email` | The newer app authentication flow using magic email links. A limited set of permissions support this. |

Where a permission only supports `web-session`, it can only be used through the web interface. Permissions that also support `app-v1` or `app-v2-magic-email` can be exercised through the respective mobile apps as well.

---

## Instance permissions

Instance permissions control what a user can do within a specific business. They are grouped by category below.

### Assets

| Permission Key | Type | Detail | Description | Dependencies | Caution |
|---|---|---|---|---|---|
| `ASSETS:ASSET_BARCODES:DELETE` | Delete | -- | Delete asset barcodes | `ASSETS:ASSET_BARCODES:VIEW` | -- |
| `ASSETS:ASSET_BARCODES:EDIT:ASSOCIATE_UNNASOCIATED_BARCODES_WITH_ASSETS` | Edit | Associate unassociated barcodes with assets | Associate any unassociated barcode with an asset | -- | -- |
| `ASSETS:ASSET_BARCODES:VIEW` | View | -- | View asset barcodes | -- | -- |
| `ASSETS:ASSET_BARCODES:VIEW:SCAN_IN_APP` | View | Scan in App | Scan barcodes in the mobile app | -- | -- |
| `ASSETS:ASSET_CATEGORIES:CREATE` | Create | -- | Add a new custom category | `ASSETS:ASSET_CATEGORIES:VIEW`, `ASSETS:ASSET_CATEGORIES:EDIT` | -- |
| `ASSETS:ASSET_CATEGORIES:DELETE` | Delete | -- | Delete a custom category | `ASSETS:ASSET_CATEGORIES:VIEW` | -- |
| `ASSETS:ASSET_CATEGORIES:EDIT` | Edit | -- | Edit a custom category | `ASSETS:ASSET_CATEGORIES:VIEW` | -- |
| `ASSETS:ASSET_CATEGORIES:VIEW` | View | -- | View a list of custom categories | -- | -- |
| `ASSETS:ASSET_FILE_ATTACHMENTS:CREATE` | Create | -- | Upload asset file attachments | -- | -- |
| `ASSETS:ASSET_FILE_ATTACHMENTS:VIEW` | View | -- | View asset file attachments | -- | -- |
| `ASSETS:ASSET_GROUPS:CREATE` | Create | -- | Create a new asset group | -- | -- |
| `ASSETS:ASSET_GROUPS:DELETE:ASSETS_WITHIN_GROUP` | Delete | Assets within Group | Delete assets within a group | -- | -- |
| `ASSETS:ASSET_GROUPS:EDIT` | Edit | -- | Edit an existing asset group | -- | -- |
| `ASSETS:ASSET_GROUPS:EDIT:ASSETS_WITHIN_GROUP` | Edit | Assets within Group | Add or remove group members | -- | -- |
| `ASSETS:ASSET_TYPE_FILE_ATTACHMENTS:CREATE` | Create | -- | Upload asset type file attachments | `ASSETS:ASSET_TYPE_FILE_ATTACHMENTS:VIEW` | -- |
| `ASSETS:ASSET_TYPE_FILE_ATTACHMENTS:VIEW` | View | -- | View asset type file attachments | -- | -- |
| `ASSETS:ASSET_TYPES:CREATE` | Create | -- | Create a new asset type | `ASSETS:CREATE` | -- |
| `ASSETS:ASSET_TYPES:EDIT` | Edit | -- | Edit an asset type | -- | -- |
| `ASSETS:ARCHIVE` | Archive | -- | Archive assets | -- | -- |
| `ASSETS:CREATE` | Create | -- | Create new assets | -- | -- |
| `ASSETS:DELETE` | Delete | -- | Delete assets | -- | -- |
| `ASSETS:EDIT` | Edit | -- | Edit assets | -- | -- |
| `ASSETS:EDIT:OVVERRIDES` | Edit | Overrides | Edit asset overrides | `ASSETS:EDIT` | -- |
| `ASSETS:FILE_ATTACHMENTS:DELETE` | Delete | -- | Delete a file attachment | -- | -- |
| `ASSETS:FILE_ATTACHMENTS:EDIT` | Edit | -- | Rename a file attachment | -- | -- |
| `ASSETS:MANUFACTURERS:CREATE` | Create | -- | Create a new manufacturer | -- | -- |
| `ASSETS:MANUFACTURERS:EDIT` | Edit | -- | Edit a manufacturer | -- | -- |
| `ASSETS:TRANSFER` | Transfer | -- | Transfer assets to another business | `ASSETS:CREATE`, `ASSETS:EDIT`, `ASSETS:DELETE`, `ASSETS:ASSET_TYPES:CREATE`, `ASSETS:ASSET_TYPES:EDIT`, `ASSETS:ASSET_CATEGORIES:VIEW`, `ASSETS:MANUFACTURERS:CREATE` | Allows user to transfer assets to another business |

:::caution
The `ASSETS:TRANSFER` permission allows a user to move assets out of your business entirely. Only grant this to trusted users.
:::

### Business

| Permission Key | Type | Detail | Description | Dependencies | Caution |
|---|---|---|---|---|---|
| `BUSINESS:BUSINESS_SETTINGS:EDIT` | Edit | -- | Edit business settings | `BUSINESS:BUSINESS_SETTINGS:VIEW` | -- |
| `BUSINESS:BUSINESS_SETTINGS:VIEW` | View | -- | View business settings page | -- | -- |
| `BUSINESS:BUSINESS_STATS:VIEW` | View | -- | View business statistics | -- | -- |
| `BUSINESS:ROLES_AND_PERMISSIONS:CREATE` | Create | -- | Add new role groups | `BUSINESS:ROLES_AND_PERMISSIONS:VIEW` | -- |
| `BUSINESS:ROLES_AND_PERMISSIONS:EDIT` | Edit | -- | Edit role group permissions | -- | Super Administrator position -- can give anyone (including themselves) any permissions |
| `BUSINESS:ROLES_AND_PERMISSIONS:VIEW` | View | -- | View a list of roles and their permissions | -- | -- |
| `BUSINESS:SETTINGS:EDIT:TRUSTED_DOMAINS` | Edit | Trusted Domains | Manage trusted domains for automatic user sign-up | -- | -- |
| `BUSINESS:USER_SIGNUP_CODES:CREATE` | Create | -- | Add a new signup code | `BUSINESS:USER_SIGNUP_CODES:VIEW` | -- |
| `BUSINESS:USER_SIGNUP_CODES:DELETE` | Delete | -- | Delete a signup code | `BUSINESS:USER_SIGNUP_CODES:VIEW` | -- |
| `BUSINESS:USER_SIGNUP_CODES:EDIT` | Edit | -- | Edit a signup code | `BUSINESS:USER_SIGNUP_CODES:VIEW` | -- |
| `BUSINESS:USER_SIGNUP_CODES:VIEW` | View | -- | View a list of signup codes | -- | -- |
| `BUSINESS:USERS:CREATE:ADD_USER_BY_EMAIL` | Create | Add user by email | Add a user to the business by email address | `BUSINESS:USERS:VIEW:LIST` | -- |
| `BUSINESS:USERS:DELETE:REMOVE_FORM_BUSINESS` | Delete | Remove from business | Remove a user from the business | `BUSINESS:USERS:VIEW:LIST` | -- |
| `BUSINESS:USERS:EDIT:CHANGE_ROLE` | Edit | Change role | Change a user's role within the business | `BUSINESS:USERS:VIEW:LIST` | Allows user to change their own role to any role |
| `BUSINESS:USERS:EDIT:USER_THUMBNAIL` | Edit | User Thumbnail | Set a user's thumbnail image | `BUSINESS:USERS:DELETE:REMOVE_FORM_BUSINESS` | -- |
| `BUSINESS:USERS:EDIT:ARCHIVE` | Edit | Archive | Archive a user within the business | `BUSINESS:USERS:VIEW:LIST` | -- |
| `BUSINESS:USERS:EDIT:ROLES_AND_PERMISSIONS` | Edit | Roles and Permissions | Change a user's role group | `BUSINESS:USERS:DELETE:REMOVE_FORM_BUSINESS` | -- |
| `BUSINESS:USERS:VIEW:LIST` | View | List | View a list of users in the business | -- | -- |
| `BUSINESS:USERS:VIEW:INDIVIDUAL_USER` | View | Individual User | View details about a specific user | -- | -- |

:::caution
The `BUSINESS:ROLES_AND_PERMISSIONS:EDIT` permission is effectively a **super administrator** permission. A user with this permission can grant themselves (or others) any permission, so only assign it to fully trusted administrators.
:::

### Clients

| Permission Key | Type | Detail | Description | Dependencies | Caution |
|---|---|---|---|---|---|
| `CLIENTS:CREATE` | Create | -- | Create a new client | `CLIENTS:VIEW` | -- |
| `CLIENTS:EDIT` | Edit | -- | Edit client details | `CLIENTS:VIEW` | -- |
| `CLIENTS:VIEW` | View | -- | View a list of clients | -- | -- |

### CMS

| Permission Key | Type | Detail | Description | Dependencies | Caution |
|---|---|---|---|---|---|
| `CMS:CMS_PAGES:CREATE` | Create | -- | Create and manage CMS pages | -- | -- |
| `CMS:CMS_PAGES:EDIT` | Edit | -- | Edit any CMS page | `CMS:CMS_PAGES:CREATE` | -- |
| `CMS:CMS_PAGES:EDIT:CUSTOM_DASHBOARDS` | Edit | Custom Dashboards | Manage custom dashboards | `CMS:CMS_PAGES:CREATE` | -- |
| `CMS:CMS_PAGES:VIEW:ACCESS_LOG` | View | Access Log | View CMS page access logs | `CMS:CMS_PAGES:CREATE` | -- |

### Files

| Permission Key | Type | Detail | Description | Dependencies | Caution |
|---|---|---|---|---|---|
| `FILES:FILE_ATTACHMENTS:EDIT:SHARING_SETTINGS` | Edit | Sharing Settings | Manage a file's sharing status | -- | -- |

### Finance

| Permission Key | Type | Detail | Description | Dependencies | Caution |
|---|---|---|---|---|---|
| `FINANCE:PAYMENTS_LEDGER:VIEW` | View | -- | View a list of payments for all projects | -- | -- |

### Locations

| Permission Key | Type | Detail | Description | Dependencies | Caution |
|---|---|---|---|---|---|
| `LOCATIONS:LOCATION_BARCODES:VIEW` | View | -- | View location barcodes | `LOCATIONS:VIEW` | -- |
| `LOCATIONS:LOCATION_FILE_ATTACHMENTS:CREATE` | Create | -- | Upload location file attachments | `LOCATIONS:LOCATION_FILE_ATTACHMENTS:VIEW` | -- |
| `LOCATIONS:LOCATION_FILE_ATTACHMENTS:VIEW` | View | -- | View location file attachments | `LOCATIONS:VIEW` | -- |
| `LOCATIONS:CREATE` | Create | -- | Add a new location | `LOCATIONS:VIEW` | -- |
| `LOCATIONS:EDIT` | Edit | -- | Edit a location | `LOCATIONS:VIEW` | -- |
| `LOCATIONS:VIEW` | View | -- | View a list of locations | -- | -- |

### Maintenance Jobs

| Permission Key | Type | Detail | Description | Dependencies | Caution |
|---|---|---|---|---|---|
| `MAINTENANCE_JOBS:DELETE` | Delete | -- | Delete a maintenance job | `MAINTENANCE_JOBS:VIEW` | -- |
| `MAINTENANCE_JOBS:EDIT:JOB_DUE_DATE` | Edit | Job Due Date | Change a job's due date | `MAINTENANCE_JOBS:VIEW` | -- |
| `MAINTENANCE_JOBS:EDIT:USER_ASSIGNED_TO_JOB` | Edit | User Assigned to Job | Change the user assigned to a job | `MAINTENANCE_JOBS:VIEW` | -- |
| `MAINTENANCE_JOBS:EDIT:USERS_TAGGED_IN_JOB` | Edit | Users Tagged in Job | Edit users tagged in a job | `MAINTENANCE_JOBS:VIEW` | -- |
| `MAINTENANCE_JOBS:EDIT:NAME` | Edit | Name | Edit the job name | `MAINTENANCE_JOBS:VIEW` | -- |
| `MAINTENANCE_JOBS:EDIT:ADD_MESSAGE_TO_JOB` | Edit | Add Message to Job | Add a message to a job | `MAINTENANCE_JOBS:VIEW` | -- |
| `MAINTENANCE_JOBS:EDIT:STATUS` | Edit | Status | Change a job's status | `MAINTENANCE_JOBS:VIEW` | -- |
| `MAINTENANCE_JOBS:EDIT:ADD_ASSETS` | Edit | Add Assets | Add assets to a job | `MAINTENANCE_JOBS:VIEW` | -- |
| `MAINTENANCE_JOBS:EDIT` | Edit | -- | Remove assets from a job | `MAINTENANCE_JOBS:VIEW` | -- |
| `MAINTENANCE_JOBS:EDIT:JOB_PRIORITY` | Edit | Job Priority | Change a job's priority | `MAINTENANCE_JOBS:VIEW` | -- |
| `MAINTENANCE_JOBS:EDIT:ASSET_FLAGS` | Edit | Asset Flags | Flag assets against a job | -- | -- |
| `MAINTENANCE_JOBS:EDIT:ASSET_BLOCKS` | Edit | Asset Blocks | Block asset assignments via a job | -- | -- |
| `MAINTENANCE_JOBS:VIEW` | View | -- | Access the maintenance jobs list | -- | -- |
| `MAINTENANCE_JOBS:MAINTENANCE_JOBS_FILE_ATTACHMENTS:CREATE` | Create | -- | Upload files to a job | `MAINTENANCE_JOBS:VIEW`, `MAINTENANCE_JOBS:EDIT:ADD_MESSAGE_TO_JOB` | -- |

### Projects

| Permission Key | Type | Detail | Description | Dependencies | Caution |
|---|---|---|---|---|---|
| `PROJECTS:PROJECT_ASSETS:CREATE:ASSIGN_AND_UNASSIGN` | Create | Assign and Unassign | Assign or unassign assets to a project | `PROJECTS:VIEW` | -- |
| `PROJECTS:PROJECT_ASSETS:CREATE:ASSIGN_ALL_BUSINESS_ASSETS` | Create | Assign all Business Assets | Assign all assets in the business to a project | `PROJECTS:PROJECT_ASSETS:CREATE:ASSIGN_AND_UNASSIGN` | -- |
| `PROJECTS:PROJECT_ASSETS:EDIT:ASSIGNMNET_COMMENT` | Edit | Assignment Comment | Edit an asset assignment comment | -- | -- |
| `PROJECTS:PROJECT_ASSETS:EDIT:CUSTOM_PRICE` | Edit | Custom Price | Edit an asset assignment's custom price | -- | -- |
| `PROJECTS:PROJECT_ASSETS:EDIT:DISCOUNT` | Edit | Discount | Edit an asset assignment discount | -- | -- |
| `PROJECTS:PROJECT_ASSETS:EDIT:ASSIGNMENT_STATUS` | Edit | Assignment Status | Change the assignment status for an asset (e.g. mark as packed) | -- | -- |
| `PROJECTS:PROJECT_CREW:CREATE` | Create | -- | Add crew to a project | `PROJECTS:PROJECT_CREW:VIEW` | -- |
| `PROJECTS:PROJECT_CREW:EDIT` | Edit | -- | Edit or delete crew assignments | `PROJECTS:PROJECT_CREW:VIEW` | -- |
| `PROJECTS:PROJECT_CREW:EDIT:CREW_RANKS` | Edit | Crew Ranks | Edit crew ranks | `PROJECTS:PROJECT_CREW:VIEW` | -- |
| `PROJECTS:PROJECT_CREW:EDIT:CREW_RECRUITMENT` | Edit | Crew Recruitment | Manage crew recruitment for a project | `PROJECTS:PROJECT_CREW:VIEW` | -- |
| `PROJECTS:PROJECT_CREW:VIEW:VIEW_AND_APPLY_FOR_CREW_ROLES` | View | View and Apply for Crew Roles | View and apply for crew roles on the recruitment page | `PROJECTS:VIEW` | -- |
| `PROJECTS:PROJECT_CREW:VIEW` | View | -- | View project crew | -- | -- |
| `PROJECTS:PROJECT_CREW:VIEW:EMAIL_CREW` | View | Email Crew | Email project crew members | `PROJECTS:PROJECT_CREW:VIEW` | -- |
| `PROJECTS:PROJECT_FLIE_ATTACHMENTS:CREATE` | Create | -- | Upload project file attachments | -- | -- |
| `PROJECTS:PROJECT_NOTES:CREATE:NOTES` | Create | Notes | Add project notes | `PROJECTS:PROJECT_NOTES:EDIT:NOTES` | -- |
| `PROJECTS:PROJECT_NOTES:EDIT:NOTES` | Edit | Notes | Edit project notes | `PROJECTS:VIEW` | -- |
| `PROJECTS:PROJECT_PAYMENTS:CREATE` | Create | -- | Add a new project payment | `PROJECTS:PROJECT_PAYMENTS:VIEW` | -- |
| `PROJECTS:PROJECT_PAYMENTS:CREATE:FILE_ATTACHMENTS` | Create | File Attachments | Upload payment file attachments | `FINANCE:PAYMENTS_LEDGER:VIEW` | -- |
| `PROJECTS:PROJECT_PAYMENTS:DELETE` | Delete | -- | Delete a project payment | `PROJECTS:PROJECT_PAYMENTS:VIEW` | -- |
| `PROJECTS:PROJECT_PAYMENTS:VIEW` | View | -- | View project payments | `PROJECTS:VIEW` | -- |
| `PROJECTS:PROJECT_PAYMENTS:VIEW:FILE_ATTACHMENTS` | View | File Attachments | View payment file attachments | `FINANCE:PAYMENTS_LEDGER:VIEW` | -- |
| `PROJECTS:PROJECT_STATUSES:CREATE` | Create | -- | Add a new project status | `PROJECTS:PROJECT_STATUSES:VIEW`, `PROJECTS:PROJECT_STATUSES:EDIT` | -- |
| `PROJECTS:PROJECT_STATUSES:DELETE` | Delete | -- | Delete a project status | `PROJECTS:PROJECT_STATUSES:VIEW`, `PROJECTS:PROJECT_STATUSES:EDIT` | -- |
| `PROJECTS:PROJECT_STATUSES:EDIT` | Edit | -- | Edit project statuses | `PROJECTS:PROJECT_STATUSES:VIEW` | -- |
| `PROJECTS:PROJECT_STATUSES:VIEW` | View | -- | View a list of project statuses | -- | -- |
| `PROJECTS:PROJECT_TYPES:CREATE` | Create | -- | Add a new project type | `PROJECTS:PROJECT_TYPES:VIEW` | -- |
| `PROJECTS:PROJECT_TYPES:DELETE` | Delete | -- | Delete a project type | `PROJECTS:PROJECT_TYPES:EDIT`, `PROJECTS:PROJECT_TYPES:VIEW` | -- |
| `PROJECTS:PROJECT_TYPES:EDIT` | Edit | -- | Edit a project type | `PROJECTS:PROJECT_TYPES:VIEW` | -- |
| `PROJECTS:PROJECT_TYPES:VIEW` | View | -- | View a list of project types | -- | -- |
| `PROJECTS:ARCHIVE` | Archive | -- | Archive a project | `PROJECTS:VIEW` | -- |
| `PROJECTS:CREATE` | Create | -- | Create a new project | `PROJECTS:VIEW`, `PROJECTS:EDIT:PROJECT_TYPE`, `PROJECTS:EDIT:LEAD` | -- |
| `PROJECTS:DELETE` | Delete | -- | Delete a project | `PROJECTS:VIEW` | -- |
| `PROJECTS:EDIT:CLIENT` | Edit | Client | Change a project's client | `PROJECTS:VIEW` | -- |
| `PROJECTS:EDIT:LEAD` | Edit | Lead | Change a project's lead | `PROJECTS:VIEW` | -- |
| `PROJECTS:EDIT:DESCRIPTION_AND_SUB_PROJECTS` | Edit | Description and Sub Projects | Change a project's description and sub-projects | `PROJECTS:VIEW` | -- |
| `PROJECTS:EDIT:DATES` | Edit | Dates | Change a project's dates | `PROJECTS:VIEW` | -- |
| `PROJECTS:EDIT:NAME` | Edit | Name | Change a project's name | `PROJECTS:VIEW` | -- |
| `PROJECTS:EDIT:STATUS` | Edit | Status | Change a project's status | `PROJECTS:VIEW`, `PROJECTS:PROJECT_STATUSES:VIEW` | -- |
| `PROJECTS:EDIT:ADDRESS` | Edit | Address | Change a project's address | `PROJECTS:VIEW` | -- |
| `PROJECTS:EDIT:INVOICE_NOTES` | Edit | Invoice Notes | Change a project's invoice notes | `PROJECTS:VIEW` | -- |
| `PROJECTS:EDIT:DELIVERY_NOTES` | Edit | Delivery Notes | Change a project's delivery notes | `PROJECTS:VIEW` | -- |
| `PROJECTS:EDIT:PROJECT_TYPE` | Edit | Project Type | Change a project's type | -- | -- |
| `PROJECTS:VIEW` | View | -- | View projects | -- | -- |

### Training

| Permission Key | Type | Detail | Description | Dependencies | Caution |
|---|---|---|---|---|---|
| `TRAINING:CREATE` | Create | -- | Add a training module | `TRAINING:EDIT` | -- |
| `TRAINING:EDIT` | Edit | -- | Edit training modules | `TRAINING:VIEW:DRAFT_MODULES` | -- |
| `TRAINING:EDIT:CERTIFY_USER` | Edit | Certify User | Certify a user's training | `TRAINING:VIEW:USER_PROGRESS_IN_MODULES` | -- |
| `TRAINING:EDIT:REVOKE_USER_CERTIFICATION` | Edit | Revoke User Certification | Revoke a user's training certification | `TRAINING:EDIT:CERTIFY_USER` | -- |
| `TRAINING:VIEW` | View | -- | Access the training page to complete training | -- | -- |
| `TRAINING:VIEW:DRAFT_MODULES` | View | Draft Modules | View draft (unpublished) training modules | `TRAINING:VIEW` | -- |
| `TRAINING:VIEW:USER_PROGRESS_IN_MODULES` | View | User progress in modules | View a list of users that have completed a training module | `BUSINESS:USERS:VIEW:LIST`, `TRAINING:VIEW` | -- |

---

## Server permissions

Server permissions are platform-level permissions intended for system administrators. These are not tied to a specific business and instead govern actions across the entire AdamRMS server.

:::note
Server permissions are only relevant if you are a platform administrator or are self-hosting AdamRMS. Most users will not need these.
:::

### Assets

| Permission Key | Type | Detail | Description | Dependencies | Caution |
|---|---|---|---|---|---|
| `ASSETS:EDIT:ANY_ASSET_TYPE` | Edit | Any asset type (including those with no instance ID) | Edit any asset type, including system-level asset types written by AdamRMS | -- | -- |

### User Management

| Permission Key | Type | Detail | Description | Dependencies | Caution |
|---|---|---|---|---|---|
| `USERS:VIEW` | View | -- | Access a list of all users on the server | -- | -- |
| `USERS:EDIT` | Edit | -- | Edit details about any user | -- | -- |
| `USERS:EDIT:THUMBNAIL` | Edit | Thumbnail | Set any user's thumbnail | `USERS:EDIT` | -- |
| `USERS:EDIT:NOTIFICATION_SETTINGS` | Edit | Notification Settings | Change another user's notification settings | `USERS:EDIT` | -- |
| `USERS:EDIT:SUSPEND` | Edit | Suspend | Suspend a user | `USERS:VIEW` | -- |
| `USERS:DELETE` | Delete | -- | Delete a user | `USERS:VIEW` | -- |
| `USERS:VIEW:MAILINGS` | View | Mailings | View mailings for a user | `USERS:VIEW` | -- |
| `USERS:VIEW_SITE_AS` | -- | View Site As | View the site as another user | `USERS:VIEW`, `USERS:EDIT`, `USERS:VIEW:MAILINGS`, `USERS:EDIT:SUSPEND` | -- |

### Permissions Management

| Permission Key | Type | Detail | Description | Dependencies | Caution |
|---|---|---|---|---|---|
| `USERS:VIEW:OWN_POSITIONS` | View | Own Positions | View your own server-level positions | -- | -- |
| `PERMISSIONS:VIEW` | View | -- | View a list of server permissions | -- | -- |
| `PERMISSIONS:EDIT` | Edit | -- | Edit server permissions | -- | -- |
| `PERMISSIONS:EDIT:USER_POSITION` | Edit | User position | Change a user's server-level permissions | `USERS:EDIT`, `USERS:VIEW:OWN_POSITIONS` | -- |

### Instances

| Permission Key | Type | Detail | Description | Dependencies | Caution |
|---|---|---|---|---|---|
| `INSTANCES:VIEW` | View | -- | Access a list of all instances (businesses) | -- | -- |
| `INSTANCES:CREATE` | Create | Has no impact if `NEW_INSTANCE_ENABLED` is set in configuration | Create a new instance | -- | -- |
| `INSTANCES:FULL_PERMISSIONS_IN_INSTANCE` | -- | Full Permissions in Instance | Log in to any instance with full permissions | `INSTANCES:VIEW` | -- |
| `INSTANCES:IMPORT:ASSETS` | Import | Import Assets to any Instance | Import assets into any instance | `INSTANCES:VIEW` | -- |
| `INSTANCES:DELETE` | Delete | -- | Delete an instance | `INSTANCES:VIEW` | -- |
| `INSTANCES:PERMANENTLY_DELETE` | Permanently Delete | -- | Permanently delete an instance (cannot be undone) | `INSTANCES:DELETE` | -- |
| `INSTANCES:EDIT` | Edit | -- | Edit instance details | `INSTANCES:VIEW` | -- |
| `USE-DEV` | -- | -- | Use the development site | -- | -- |

### General sys admin

| Permission Key | Type | Detail | Description | Dependencies | Caution |
|---|---|---|---|---|---|
| `VIEW-AUDIT-LOG` | -- | -- | View the server audit log | -- | -- |
| `VIEW-ANALYTICS` | -- | -- | View server analytics | `INSTANCES:VIEW` | -- |
| `CONFIG:SET` | -- | -- | Set system configuration values | -- | -- |

---

## Understanding dependencies

Many permissions have **dependencies** -- other permissions that must also be granted for the permission to function correctly. When you assign a permission that has dependencies, you should ensure that all of its dependencies are also assigned to the same Role Group. AdamRMS will not automatically grant dependencies for you.

For example, `PROJECTS:CREATE` depends on `PROJECTS:VIEW`, `PROJECTS:EDIT:PROJECT_TYPE`, and `PROJECTS:EDIT:LEAD`. If you grant `PROJECTS:CREATE` without also granting `PROJECTS:VIEW`, the user may not be able to access the project creation page at all.

:::tip
When building a custom Role Group, start with the "View" permissions for each category, then layer on "Edit", "Create", and "Delete" permissions as needed. This ensures that users can see the areas of the system they need before you grant them the ability to make changes.
:::
