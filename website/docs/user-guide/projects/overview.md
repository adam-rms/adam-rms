---
sidebar_position: 22
title: Project Overview
---

# Project Overview
---

The project overview page lists basic information about the project, along with any comments.

:::note Permissions Required
PROJECTS:EDIT:CLIENT  
PROJECTS:EDIT:LEAD  
PROJECTS:EDIT:DESCRIPTION_AND_SUB_PROJECTS  
PROJECTS:EDIT:DATES  
PROJECTS:EDIT:NAME  
PROJECTS:EDIT:STATUS  
PROJECTS:EDIT:ADDRESS  
:::

All projects can consist of:
- A Client
- A Project Manager
- A Venue
- Project Dates, including asset dispatch dates

If you want to assign assets to a project, all of this information must be set.

![Project Overview](/img/tutorial/projects/projects-overview.png)
*Project Overview*

Comments allow you to keep a log of updates about the project, such as venue and date  changes.

All Projects have a status, that is the overall status of the project. You can also have statuses for each asset in the project, which is discussed in the next section.

### Addtional Project Settings Menu
Many of the project settings can be updated by using functionality found in the addtional project settings menu. These include updating the name and description of a project, assigning a parent project or creating sub-projects and archiving or deleting the project. 

![Addtional Project Settings Menu](/img/tutorial/projects/projects-additional-settings.png)

## Comments & History
---

AdamRMS has a full Audit Log, which keeps track of changes to various elements of the platform. This includes projects, and the edit history of the project can be found on the history page.

![Project audit log](/img/tutorial/projects/projects-audit.png)
*Project History*

## Files, Notes, Invoices & Quotes
---
Projects can have various files associated with them, to keep track of documents and provide invoices or quotes to clients. 

:::note Permissions Required
PROJECTS:PROJECT_NOTES:EDIT:NOTES  
PROJECTS:PROJECT_NOTES:CREATE:NOTES  
PROJECTS:PROJECT_FLIE_ATTACHMENTS:CREATE  
:::

![Project Files](/img/tutorial/projects/projects-files.png)
*Notes and Projects for the project*

Notes are intended to be used for meeting notes and event plans.  

Invoices and Quotes have different footers, which can be set in [the settings page](../business/business-settings#basic-settings).  
More information about Invoices and Quotes can be found in the [finance overview](./finance).

## Sub-projects
---
Projects can have associated sub-projects, which are linked to the main project. These sub-projects act as independent projects, but are linked for organisation.

![Example sub-project](/img/tutorial/projects/projects-subproject.png)
*An example subproject*

It is recommended you set up a new Project Type for sub-projects that excludes Finance, so that financial elements are only included in the main project.

![Project types](/img/tutorial/projects/projects-subprojects-type.png)
*Suggested subproject type*


### Following Parent Project Status
Sub-projects can optionally track the status of their parent project, allowing you to update their project status automatically when the parent project is updated.

When parent project tracking is enabled, the sub-project's status will update to the parent's status, and a notice appears above the project status

![Project status tracking the parent project](/img/tutorial/projects/projects-subproject-parent-status.png)  
*A Subproject that tracks the parent's project status*

Use the addtional project settings menu to enable or disable parent status tracking on subprojects.