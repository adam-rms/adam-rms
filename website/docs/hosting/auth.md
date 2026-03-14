---
sidebar_position: 10
title: Authentication Providers
---

AdamRMS offers authentication with Google and Microsoft as an alternative authentication provider, which requires a Google Developer account and a Microsoft Azure account to generate client secrets.

We use the HybridAuth library, and its [Google Provider](https://hybridauth.github.io/hybridauth/userguide/IDProvider_info_Google.html) has good documentation on how to set this up.

## Google

To set up Google authentication, you will need to create a new project in the [Google Developer Console](https://console.developers.google.com/), and then create a new OAuth 2.0 Client ID.

You will need to set the following configuration variables in the [configuration menu](./configuration-menu.md):

### Google Auth Key

The ID key for Google authentication. When configuring Google authentication, set the redirect URIs to `https://YOURROOTURL/login/oauth/google.php` and `https://YOURROOTURL/api/account/oauth-link/google.php`

### Google Auth Secret

The secret key for Google authentication.

### Google Auth Scope

The scope for Google authentication. You would only usually change this if you are developing AdamRMS.

This is normally left as `https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email`

## Microsoft

To set up Microsoft authentication, you will need to create a new project in the [Microsoft Azure Portal](https://portal.azure.com/), and then create a new App Registration.

You will need to set the following configuration variables in the [configuration menu](./configuration-menu.md):

### Microsoft Auth App ID

The App ID key for Microsoft authentication. When configuring Microsoft authentication, set the redirect URIs to `https://YOURROOTURL/login/oauth/microsoft.php` and `https://YOURROOTURL/api/account/oauth-link/microsoft.php`

### Microsoft Auth Secret

The secret key for Microsoft authentication.
