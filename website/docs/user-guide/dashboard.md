---
sidebar_position: 3
title: Dashboard & Navigation
---

# Dashboard & Navigation

The dashboard is the first page you see after logging in to AdamRMS. It provides an overview of your business activity and quick access to all areas of the system.

## The Dashboard

The default dashboard consists of two main sections:

### Widgets

The top section of the dashboard displays **widgets** -- small cards showing key statistics and information about your business. Widgets can include metrics such as:

- Number of active projects
- Total asset count
- Upcoming project dates
- Financial summaries
- Recent activity

You can customise which widgets appear on your dashboard from the **Statistics** page in Business Settings. If no widgets have been added, the dashboard will show a prompt to add some.

:::note Permissions Required
BUSINESS:BUSINESS_STATS:VIEW
:::

:::tip
Administrators can set up [Custom Dashboards](./cms/custom-dashboards) to replace the default dashboard with a CMS page for specific role groups. This is useful for displaying tailored information to different teams.
:::

### Calendar

Below the widgets, the dashboard features a **calendar view** showing all your upcoming projects. The calendar displays project dates colour-coded by their status, giving you a visual overview of your schedule.

The calendar can be exported to external calendar applications (Google Calendar, Outlook, Apple Calendar, etc.) via the iCal export feature in your [account settings](#navigation).

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

The side menu provides access to all major sections of AdamRMS:

- **Dashboard** -- return to the home page
- **Projects** -- all non-archived projects are listed individually in the menu, with icons and colours indicating your role and the project status (see [Projects](./projects/projects) for the colour code reference)
- **Assets** -- browse and search your equipment catalogue
- **Maintenance** -- manage maintenance jobs
- **Training** -- training modules and progress
- **Locations** -- manage venues and storage locations
- **Clients** -- manage your client directory
- **Ledger** -- view payment records
- **CMS** -- content management pages (if visible to your role)
- **Business Settings** -- administration and configuration (if you have permission)

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
