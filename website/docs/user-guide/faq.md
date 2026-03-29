---
sidebar_position: 91
title: FAQ & Troubleshooting
---

# FAQ & Troubleshooting

Common questions and solutions for issues you might encounter when using AdamRMS.

---

## Account & Login

### I can't log in to my account
- **Check your email and password** -- make sure you're using the correct credentials. Passwords are case-sensitive and must be at least 12 characters.
- **Check for email verification** -- if email is enabled on your installation, you must verify your email address before logging in. Check your spam folder for the verification email.
- **Try resetting your password** -- use the "Forgot Password" link on the login page to receive a password reset email.
- **Contact your administrator** -- if your account has been suspended or if you're unable to resolve the issue, contact your AdamRMS administrator.

### I can't see certain features or pages
AdamRMS uses a [granular permission system](./business/user-management#permissions). If a feature is missing from your view, it's likely that your role group doesn't include the required permission. Contact your business administrator to request access.

### How do I switch between businesses?
Click on the **business name** in the top navigation bar. A dropdown will appear where you can select a different business or join a new one using a signup code. See [Dashboard & Navigation](./dashboard#switching-businesses).

### How do I join a new business?
There are three ways:
1. **Signup code** -- enter a code provided by the business administrator.
2. **Trusted domain** -- if your email domain matches a trusted domain configured by the business, you'll be offered to join automatically.
3. **Direct invitation** -- an administrator can add you by email from the [User Management](./business/user-management) page.

---

## Assets

### What's the difference between an asset type and an individual asset?
An **asset type** is a template (e.g. "5m XLR Cable") that holds shared information like hire rates and manufacturer. **Individual assets** are the physical items you own -- each with a unique tag (e.g. `A-0042`). See [Assets](./assets/assets#key-concepts) for a full explanation.

### Why can't I assign an asset to a project?
Check the following:
- The asset must not be **blocked** by a maintenance job.
- The asset must not already be assigned to another project during the same date range (unless the other project's status releases assets).
- The project must have **delivery dates** set.
- You need the `PROJECTS:PROJECT_ASSETS:CREATE:ASSIGN_AND_UNASSIGN` permission.

### How do I retire/archive an asset?
Set an **end date** on the individual asset. Assets with end dates in the past are considered archived and hidden from default searches. They can still be found using the "Show Archived" option. See [Assets](./assets/assets#asset-lifecycle).

### How do I track where an asset is?
Use [Asset Barcodes](./assets/asset-barcodes) and [Locations](./business/locations). Scanning an asset's barcode at a location records its position. You can also manually set a storage location on each individual asset.

---

## Projects

### How do project statuses work?
Each project has a status that reflects its stage in your workflow (e.g. Confirmed, Dispatched, Returned). Statuses are colour-coded in the menu and calendar. Your business administrator can customise available statuses in [Business Settings](./business/business-settings#project-statuses). See [Projects](./projects/projects) for the full colour code reference.

### How does asset pricing work?
AdamRMS calculates hire costs based on:
- The **day rate** and **week rate** set on the asset type (or overridden on the individual asset)
- The **hire period** (based on project delivery dates, or a custom duration)

You can override pricing per-assignment using **discounts** (percentage) or **custom prices** (fixed amount). See [Project Assets](./projects/assets#discount--custom-price).

### How do sub-projects work?
A sub-project is a project linked to a parent project. Sub-projects act as independent projects but are grouped for organisation. They can optionally track their parent's status automatically. It's recommended to use a project type that excludes finance for sub-projects. See [Project Overview](./projects/overview#sub-projects).

---

## Finance

### Where do I view all payments across my business?
Use the [Ledger](./business/ledger) page, which aggregates all payments from all projects into a single searchable view.

### How do I create an invoice or quote?
Navigate to the project's finance section, then use the Invoices & Quotes tab. You can customise what information to include and generate a PDF. Invoice and quote footers are configured in [Business Settings](./business/business-settings#basic-settings). See [Project Finance](./projects/finance).

### How do I record a payment?
Payments are recorded from within a project's finance page. See [Project Finance](./projects/finance#receiving-payments). The payment will then also appear in the business [Ledger](./business/ledger).

---

## Calendar

### How do I export my calendar to Google Calendar / Outlook / Apple Calendar?
Go to your [account page](./account#calendar-export) and copy the calendar URL from the **Export Calendar** tab. Then follow the step-by-step instructions for your calendar application.

### Why are some projects missing from the calendar?
The calendar only shows projects that are **not archived** and **not deleted**. Projects whose status has "Release Assets" enabled (e.g. Closed, Cancelled) may also be hidden. Check your [Business Settings](./business/business-settings#calendar-settings) for calendar display options.

---

## Crew

### How do I apply for a crew role?
Open crew vacancies are displayed on the dashboard vacancies page. Click on a vacancy to view the details and submit an application. See [Project Crew](./projects/crew#crew-recruitment).

### How do I get notifications when I'm added to a project?
Notifications are enabled by default for crew assignments. You can manage your notification preferences from your [account page](./account#notifications).

---

## Administration

### How do I create a new role group with limited permissions?
Go to the [User Management](./business/user-management#permissions) page, create a new Role Group, and select only the permissions you want to grant. Assign users to this role group to limit their access. See the [Permissions Reference](./business/permissions-reference) for a complete list of available permissions.

### How do I configure email sending?
Email configuration is done at the server/installation level. See the [Configuration Reference](../hosting/configuration-reference#email) for all email-related settings, or the [email setup guide](../hosting/email) for provider-specific instructions.

### Where can I find all available configuration options?
See the [Configuration Reference](../hosting/configuration-reference) for a complete list of all settings with descriptions, types, defaults, and environment variable fallbacks.

---

## Still Need Help?

If your question isn't answered here:
- Browse the rest of this [User Guide](./intro)
- Check the [API Documentation](/api/adamrms-api) for technical details
- [Submit a documentation request](https://github.com/adam-rms/adam-rms/issues/new?assignees=&labels=documentation&template=training-request.yml&title=%5BDOCS%5D) on GitHub
- Visit the [Support page](/support) for further help
