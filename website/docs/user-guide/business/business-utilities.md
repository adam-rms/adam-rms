---
sidebar_position: 31
title: Business Utilities
---

# Business Utilities

## Stats
---
Business stats are widgets that appear on a user’s dashboard, that give information about the business and a user’s tasks on the RMS.  
The stats page allows users to add and remove widgets from their dashboard.

:::note Permissions Required
BUSINESS:BUSINESS_STATS:VIEW  
:::

![Business Stats](/img/tutorial/businesses/utilities-stats.png "Business Stats Widgets")
*Business Stats Widgets*

## Calendar
---
The Business Calendar shows an overview of all projects that are currently not archived or deleted.  
It is repeated on the main Dashboard page.

![Business Calendar](/img/tutorial/businesses/utilities-calendar.png "Business Calendar")
*Business Calendar*

By default, the week numbers listed in the calendar are the week of the year. However, these can be updated on a week-by-week basis in the [Business Settings](./business-settings) page.

## Clients
---
The Clients page lists all business clients, and is where new ones are added.
There are a number of fields about each client, which includes the total amount paid to your business and how much is owed by the client

:::note Permissions Required
CLIENTS:VIEW  
CLIENTS:CREATE  
CLIENTS:EDIT  
:::

![Client List](/img/tutorial/businesses/utilities-clients.png)
*Client List*

A new client just needs a Name, other entries can then be added by editing the client.

Clients can have the following fields:
- Name
- Address
- Phone Number
- Email
- Website
- Notes - General comments about this client


## Payments
---
The Payment page is your business ledger, which tracks all payments added to the RMS.

:::note Permissions Required
FINANCE:PAYMENTS_LEDGER:VIEW  
:::

![Business Ledger](/img/tutorial/businesses/utilities-ledger.png "Business Ledger")
*List of Payments*

## Locations
---
[Projects](./../projects/overview) and [Assets](./../assets) can be assigned a location to say where they are.

![Location List](/img/tutorial/businesses/utilities-locations.png "Location List")
*List of Locations*

:::note Permissions Required
LOCATIONS:VIEW  
LOCATIONS:CREATE  
LOCATIONS:EDIT  
LOCATIONS:LOCATION_BARCODES:VIEW  
::: 

Locations consist of:
- Name
- Address
- Notes (eg. Access requirements)
- Client - Link a location to a client
- Sub Location Of - Add a hierarchical list of locations.

![New Location](/img/tutorial/businesses/utilities-locations-new.png "New Location popup")
*Adding a new location*

Each location has an AdamRMS barcode that is used to associate an asset with a location by scanning both. These can be printed to place in the location or accessed from the website to assign assets to a location.
