---
sidebar_position: 5
title: Configuration Menu
---

# Configuration Menu

The configuration menu is where you can set up the core settings of your AdamRMS instance. This includes setting up the database, configuring the email server, and setting up the storage for images and files.

You can find it by clicking configuration at the bottom of the menu on the left of the screen:

![Commands](/img/hosting-docs/finding-the-config-menu.png)

:::info
Can't find the configuration menu? If the menu does not appear, your account does not have the correct permissions to access it - you must set your account as a server administrator.
Login to AdamRMS using the default super admin account (username `username` and the password `password!` - make sure to change this) - this account can access the configuration menu.
:::

## Settings Overview

### General

- **Root URL**: The main URL of your AdamRMS instance (e.g., `https://yourdomain.com`). Must not end with a trailing slash. Critical for login functionality.
- **Timezone**: Set your server's timezone (e.g., `Europe/London`).

### Email Configuration

- **Email sending**: Toggle email functionality. When enabled, requires user email verification.
- **Email provider**: Choose between SMTP and other providers.
- **From email address**: The address emails will be sent from.
- **Email Service API key**: Required for Sendgrid, Mailgun, or Postmark.
- **Mailgun Server Location**: Select US or EU servers for Mailgun.

#### SMTP Settings

- **Server address**: Your SMTP server hostname
- **Username**: SMTP authentication username
- **Password**: SMTP authentication password
- **Port**: SMTP server port number
- **Email Footer**: Customize the footer for all outgoing emails

### Error Handling

- **Sentry.io API key**: For error tracking (typically used in development)

### Security & Login

- **JWT Key**: 64-character secret key for JWT signing. This should have been auto-generated during installation.
- **Password hashing**: Algorithm for new password hashes
- **Google Authentication**:
  - Auth Key and Secret
  - Redirect URIs: `/login/oauth/google.php` and `/api/account/oauth-link/google.php`
- **Microsoft Authentication**:
  - App ID and Secret
  - Similar redirect URI structure as Google

### Customization

- **Project name**: Override the default "AdamRMS" name
- **URLs**: Configure links for:
  - User guide
  - Support page
  - Terms of service
- **Analytics**: Add tracking code for analytics

### File Storage

- **AWS S3 Configuration**:
  - Access keys and bucket settings
  - CloudFront CDN settings
  - Endpoint configurations
  - Region settings

### Billing

- **Instance Creation**:
  - Control who can create new instances
  - Set default suspension status
  - Configure suspension messages
- **Stripe Integration**:
  - API keys and webhook configuration

### Telemetry

- **Data Collection**:
  - Configure telemetry level
  - Control installation visibility
  - Set installation notes
  - Manage NanoID for installation tracking

:::tip
For detailed setup instructions of specific features, refer to their respective documentation sections.
:::
