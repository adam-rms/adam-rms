---
sidebar_position: 1
title: DigitalOcean App Platform
---

AdamRMS can be deployed on the [DigitalOcean App Platform](https://www.digitalocean.com/products/app-platform/), a Platform as a Service (PaaS) offering that allows you to deploy, manage, and scale applications without needing to manage the underlying infrastructure.

The App Specification belwo is a simple example of how to deploy AdamRMS on the DigitalOcean App Platform. It includes a PHP service, a MySQL database, and a domain configuration.

```yaml
alerts:
  - rule: DEPLOYMENT_FAILED
  - rule: DOMAIN_FAILED
envs:
  - key: CONFIG_ROOTURL
    scope: RUN_AND_BUILD_TIME
    value: ${APP_URL}
  - key: DB_HOSTNAME
    scope: RUN_AND_BUILD_TIME
    value: DATABASEID.db.ondigitalocean.com
  - key: DB_DATABASE
    scope: RUN_AND_BUILD_TIME
    value: adamrms
  - key: DB_USERNAME
    scope: RUN_AND_BUILD_TIME
    value: adamrms
  - key: DB_PORT
    scope: RUN_AND_BUILD_TIME
    value: "DATABASEPORT"
  - key: VERSION_NUMBER
    scope: RUN_AND_BUILD_TIME
    value: ${_self.COMMIT_HASH}
  - key: DB_PASSWORD
    scope: RUN_AND_BUILD_TIME
    type: SECRET
    value: DATABASESECRET
ingress:
  rules:
    - component:
        name: adam-rms
        rewrite: /src/
      match:
        path:
          prefix: /
name: adam-rms
services:
  - environment_slug: php
    github:
      branch: main
      deploy_on_push: true
      repo: adam-rms/adam-rms
    health_check:
      http_path: /src/api/onlineCheck.php
      initial_delay_seconds: 10
      period_seconds: 5
      timeout_seconds: 1
    http_port: 8080
    instance_count: 1
    name: adam-rms
    run_command:
      "php vendor/bin/phinx migrate -e production && heroku-php-apache2 -F
      /php-fpm.conf "
    source_dir: /
```

Some examples of configuration values that need to be set are shown below.

![Commands](/img/hosting-docs/digital-ocean/commands.png)

![ENV Vars](/img/hosting-docs/digital-ocean/env-vars.png)

![HTTP Routes](/img/hosting-docs/digital-ocean/http-routes.png)

![Ports & Health](/img/hosting-docs/digital-ocean/ports-health.png)
