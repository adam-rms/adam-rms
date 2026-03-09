---
sidebar_position: 10
title: Emails
---

AdamRMS sends a variety of notifications through email and support three major email providers. You can enable emails and select which provider to use in your site's [configuration menu](./configuration-menu.md).

If there is a provider you'd like to use but isn't included here, please [create an issue](https://github.com/adam-rms/adam-rms/issues/new?assignees=&labels=enhancement&projects=&template=feature-request.yml&title=%5BFEATURE%5D+%3Ctitle%3E) in AdamRMS' Github!

## [Sendgrid](https://sendgrid.com/en-us)

For many deployments of AdamRMS the free tier provided by Sendgrid should be sufficient.

You will need to generate a [Sendgrid API Key](https://app.sendgrid.com/settings/api_keys) and store this in the [configuration menu](./configuration-menu.md) under  API key.

You must also set from email to be the email address that these emails are sent from. This should be an address that is known to Sendgrid and has had [Domain Authentication](https://docs.sendgrid.com/ui/account-and-settings/how-to-set-up-domain-authentication) set up, otherwise your emails are likely to be blocked or send to spam.


## [Mailgun](https://www.mailgun.com/)

For most installations, the free tier of Mailgun will be sufficient for the number of emails sent by AdamRMS.

Mailgun operates in two regions, US and EU. You can select a region in AdamRMS, and you'll need to generate an API Key for that region - [US Region](https://app.mailgun.com/settings/api_security) or [EU Region](https://app.eu.mailgun.com/settings/api_security).

The from email address must be in the same domain as one set up in your regions domains, and they [require domain verification](https://documentation.mailgun.com/docs/mailgun/user-manual/domains/) before you can send emails.

## [Postmark](https://postmarkapp.com/)

The free tier of Postmark allows up to 100 emails a month, which should be sufficient for many AdamRMS installations

You'll need the `Server API` token for a `Transactional Stream` on the server of your choice - the default `My First Server` provides this automatically.

Postmark recommends setting up [domain signatures](https://account.postmarkapp.com/signature_domains), but will allow you to send some emails before setting this up.

## SMTP

:::note
It is highly recommended to use one of the email providers above, rather than SMTP emails. Whilst SMTP is usable in production envionments, it is less stable than the email providers above.
:::

You will need an SMTP server that is accessible to AdamRMS. The domain name or ip address of the server should be provided in your installation's settings, alongside the port and optionally a username and password. If you don't provide a username and password, SMTP emails will be sent to your server without authentication