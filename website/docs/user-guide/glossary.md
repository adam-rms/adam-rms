---
sidebar_position: 90
title: Glossary
---

# Glossary

A reference of key terms used throughout AdamRMS.

---

### Asset
A piece of equipment managed in AdamRMS. See [Assets](./assets/assets) for more details.

### Asset Tag
A unique identifier assigned to each individual asset, either auto-generated (format `A-XXXX`) or manually entered. Used to distinguish between individual items of the same asset type.

### Asset Type
A template describing a kind of equipment (e.g. "ETC Source Four Jr"). Asset types hold shared information like hire rates, value, manufacturer, and custom field definitions. Multiple individual assets can belong to the same asset type.

### Block
A restriction on an asset that prevents it from being assigned to projects. Blocks are typically created by maintenance jobs. Compare with **Flag**.

### Business
The organisational unit in AdamRMS. Each business has its own assets, projects, clients, users, and settings. A single AdamRMS account can belong to multiple businesses. Also referred to as an **Instance** in technical documentation.

### Category
A classification for asset types, used to organise equipment into logical groups (e.g. "Lighting - Conventionals", "Sound - Microphones"). Categories belong to **Category Groups**.

### Category Group
A top-level grouping of categories (e.g. "Lighting", "Sound", "Video"). Category groups help organise the asset catalogue into broad sections.

### Client
An organisation or individual that you hire equipment to. Clients can be linked to projects and locations. See [Clients](./business/clients).

### CMS Page
A content management page within AdamRMS that can display custom content to users. CMS pages can be shown in the sidebar, used as custom dashboards, or organised into a page hierarchy. See [CMS Pages](./cms/cms-pages).

### Crew
The people assigned to work on a project. Crew members can be assigned roles and managed through the project's crew section. See [Crew](./projects/crew).

### Custom Dashboard
A CMS page configured to replace the default dashboard for a specific role group. See [Custom Dashboards](./cms/custom-dashboards).

### Custom Fields
Up to 10 user-defined fields on an asset type that can hold per-asset data (e.g. "Serial Number", "Cable Length", "Colour"). Also called **Definable Fields**.

### Dispatch
The process of sending assets out for a project. Assets move through various statuses during a project lifecycle, and dispatch tracking helps manage this workflow. See [Project Assets](./projects/assets).

### Flag
An informational warning on an asset (e.g. "requires PAT testing"). Flags appear as alerts but do not prevent the asset from being assigned to projects. Compare with **Block**.

### Instance
The technical term for a **Business** in AdamRMS. Each instance is a separate tenant with its own data, users, and configuration. The terms "instance" and "business" are used interchangeably.

### Ledger
A centralised view of all payment records across your business. See [Ledger](./business/ledger).

### Linked Asset
An asset that is linked to a parent asset, so that both are assigned together when the parent is added to a project. For example, a lamp might be linked to its flight case.

### Location
A physical place such as a venue, warehouse, or storage area. Locations can be hierarchical (sub-locations) and have barcodes for asset tracking. See [Locations](./business/locations).

### Maintenance Job
A task created to track repair, servicing, or inspection work on one or more assets. Maintenance jobs can block assets from being assigned until the work is complete. See [Maintenance](./assets/maintenance).

### Manufacturer
The company that produces a piece of equipment. Manufacturers are linked to asset types.

### Permission
A granular access control that determines what a user can do within a business. Permissions are assigned to **Role Groups**. See [User Management](./business/user-management#permissions).

### Project
A date-based event or job that brings together assets, crew, locations, and finances. Projects are the primary organisational unit for tracking work. See [Projects](./projects/projects).

### Project Manager
The user designated as the lead for a project. Project managers are indicated with a star icon in the sidebar menu.

### Project Status
The current state of a project in its lifecycle. Statuses include: Added to RMS, Targeted, Quote Sent, Confirmed, Prep, Dispatched, Returned, Closed, Cancelled, and Lead Lost. Each status has a colour code used throughout the interface.

### Project Type
A classification for projects that determines their behaviour, such as whether finance tracking is enabled. Project types are configured per-business.

### Role Group
A named set of permissions that can be assigned to users within a business. Examples include "Administrator", "Technician", "Viewer". See [User Management](./business/user-management#permissions).

### Signup Code
A code that can be shared with new users to allow them to join your business with a specific role group. See [User Management](./business/user-management#signup-codes).

### Sub-Project
A project that exists under a parent project, allowing complex events to be broken down into manageable sections whilst sharing an overall timeline.

### Trusted Domain
An email domain (e.g. `@yourcompany.com`) configured so that users with matching verified email addresses can automatically join your business. See [User Management](./business/user-management#trusted-domains).

### Widget
A small dashboard card showing a statistic or piece of information about your business (e.g. total assets, active projects, financial summary). Widgets can be customised from Business Settings.
