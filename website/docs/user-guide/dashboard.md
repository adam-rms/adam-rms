---
sidebar_position: 3
title: Dashboard & Navigation
---

# Dashboard & Navigation

The dashboard is the first page you see after logging in to AdamRMS. It provides an overview of your business activity and quick access to all areas of the system.

## The Dashboard

The default dashboard consists of two main sections:

### Widgets

The top section of the dashboard displays **widgets** -- small cards showing key statistics and information about your business. Available widgets include:

- Inventory value graph
- Inventory totals (total value and mass)
- Storage usage
- User count
- Outstanding maintenance jobs
- Your maintenance jobs
- Your personal calendar

You can customise which widgets appear on your dashboard from the **Statistics** page in Business Settings. If no widgets have been added, the dashboard will show a prompt to add some.

:::note Permissions Required
`BUSINESS:BUSINESS_STATS:VIEW` is required to access the Statistics page where you manage which widgets are shown.
:::

:::tip
Administrators can set up [Custom Dashboards](./cms/custom-dashboards) to replace the default dashboard with a CMS page for specific role groups. This is useful for displaying tailored information to different teams.
:::

### Calendar

Below the widgets, the dashboard features a **calendar view** showing all projects in your business that are not archived or deleted. The calendar displays project dates colour-coded by their status, giving you a visual overview of your schedule.

The calendar can be exported to external calendar applications (Google Calendar, Outlook, Apple Calendar, etc.) via the iCal export feature on your [account page](./account#calendar-export).

## Navigation

AdamRMS uses a combination of a top navigation bar and a side menu to help you move around the system.

### Top Navigation Bar

![Top Menu](/img/tutorial/base/start-menu.png "Top Navigation Bar")

The top bar contains the following controls (left to right):

- **Menu toggle** -- show or hide the side menu
- **Logout** -- sign out of your account
- **Project search** -- quickly find and jump to a project
- **Business name** -- shows your current business; click to switch between businesses or join a new one
- **Light/Dark mode** -- toggle between light and dark themes
- **Asset search** -- open the [asset search](./assets/finding-assets) page

### Side Menu

The side menu provides access to all major sections of AdamRMS, organised under headers:

- **Dashboard** -- return to the home page
- **Business** -- expands to show business-wide tools:
  - **Stats** -- business statistics and [dashboard widget](./business/business-utilities#statistics--widgets) configuration
  - **Calendar** -- business-wide calendar view
  - **Clients** -- manage your [client directory](./business/clients)
  - **Payments** -- view [payment records](./business/ledger) (the ledger)
  - **Locations** -- manage [venues and storage locations](./business/locations)
  - **Manufacturers** -- manage [equipment manufacturers](./assets/manufacturers)
  - **CMS Pages** -- [content management pages](./cms/cms-pages) (if visible to your role)
  - **Users & Settings** -- [user management](./business/user-management) and [business configuration](./business/business-settings) (if you have permission)
- **ASSETS** header:
  - **Assets** -- browse and [search your equipment](./assets/finding-assets) catalogue
  - **New Asset** -- [create a new asset type](./assets/new-assets)
  - **Groups** -- manage [asset groups](./assets/asset-groups)
  - **Maintenance** -- manage [maintenance jobs](./assets/maintenance)
  - **Barcode Scanner** -- scan [asset barcodes](./assets/asset-barcodes)
- **PROJECTS** header:
  - All non-archived projects are listed individually, with icons and colours indicating your role and the project status (see [Projects](./projects/projects) for the colour code reference)
  - **Training** -- [training modules](./training/training) and progress

:::caution Please note
AdamRMS uses a granular permission management system, so the items visible in your side menu depend on the permissions assigned to your role group. You may not see all of the sections listed above.
:::

## Switching Businesses

If your account belongs to multiple businesses, you can switch between them by clicking on the **business name** in the top navigation bar. This opens a dropdown where you can:

- Select a different business to switch to
- Join a new business using a signup code
- See businesses available via your email domain (trusted domains)

## Related Features

- [Getting Started](./getting-started) -- setting up your account
- [Custom Dashboards](./cms/custom-dashboards) -- replacing the default dashboard for specific role groups
- [Search](./search/search) -- using the global search
- [Business Settings](./business/business-settings) -- configuring your business
