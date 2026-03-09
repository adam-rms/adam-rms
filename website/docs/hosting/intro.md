---
sidebar_position: 1
title: Self-Hosting
---

import Tabs from "@theme/Tabs";
import TabItem from "@theme/TabItem";

# Self-Hosting AdamRMS

The AdamRMS dashboard is packaged as a Docker image, which is stored in the Github Image repository. We recommend you deploy this on managed infrastructure (such as AWS or DigitalOcean), but this guide will be generic, and not make use of platform-specific tools where possible.

:::note Before you start
This guide assumes you have a good understanding of deploying web infrastructure, and it will not give specific commands, or guidance for how to manage areas such as DNS.
:::

:::info
AdamRMS is distributed under the [GNU Affero General Public License v3.0](https://github.com/adam-rms/adam-rms/blob/main/LICENSE) and therefore is provided "AS IS".  
The documentation on this page is delivered as guidance and advice, and it is believed to be correct and working at the time of writing. Any comments are greatly appreciated, and should be [submitted as an issue.](https://github.com/adam-rms/website/issues/new?assignees=&labels=documentation&projects=&template=doc_issue.yaml&title=%5BDocs+Issue%5D+%3Ctitle%3E)
:::

## What You need

- A Domain AdamRMS will be deployed to - eg. `dash.adam-rms.com`
- [The AdamRMS docker image](https://github.com/adam-rms/adam-rms/pkgs/container/adam-rms)
- A MySQL or MariaDB Database

### Optional Features you can setup later on

- [S3-compatible storage](./s3-storage) - if you want to store images & files
- [A SendGrid account](./sendgrid) - for emails
- [Developer Google and Microsoft accounts](./auth) - for Advanced sign-in options
- [Sentry](./sentry) - for error logging

## The Container

The container is based on the PHP Apache2 image, and requires a number of environment variables to be set.

- `DB_HOSTNAME` the hostname of the database that the docker container can reach
- `DB_DATABASE` the name of the database
- `DB_USERNAME` the username that the container will use to connect to the database
- `DB_PASSWORD` the password for the database user
- `DB_PORT` the port that the database is listening on, will default to `3306`

The Docker image of AdamRMS can be found in the project's [Github package repository](https://github.com/adam-rms/adam-rms/pkgs/container/adam-rms), and you can pull it by running:

```sh
docker pull ghcr.io/adam-rms/adam-rms:latest
```

A MySQL-compatible database is required by the docker image, and we know of success with both MySQL and MariaDB deployments. As with all projects, we recommend you follow security best practices when it comes to your database, as this will be the main store of user data.

Database migrations are applied each time the docker image is run, and so updates will be automatically applied.

## Docker Compose

We have provided a [simple docker-compose file](./minimal-docker-compose) that will start the AdamRMS container and a MySQL container. This is a good starting point for a simple environment, but we recommend you use a more advanced setup for production - we've given an example of this in the [advanced docker-compose file](./advanced-docker-compose).

:::warning
A new installation of AdamRMS will, by default, create a new user with the username `username` and the password `password!`. This user will have full access to the system, and so you must login to this account and change the password as soon as possible.
:::
