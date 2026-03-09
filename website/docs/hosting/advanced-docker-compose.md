---
sidebar_position: 4
title: Advanced Docker Compose
---

This is a more advanced version of the [simple Docker Compose configuration](./minimal-docker-compose) for running AdamRMS with a MySQL database. This configuration is suitable for production use, and includes additional services such as a cloudflared tunnel, datadog monitoring, and mysql backups.

Place the following `docker-compose.yml` file in the same directory as the `.env` file below, and then run `docker compose up -d` to start the services.

```yaml
services:
  db:
    image: index.docker.io/mysql/mysql-server:8.0
    command: --default-authentication-plugin=mysql_native_password --innodb-thread-concurrency=0 --sort_buffer_size=512K
    container_name: db
    ports: # Disable this if you would like to keep the database on the local machine only (recommended)
      - 3306:3306
    volumes:
      - db_data:/var/lib/mysql
      - /etc/localtime:/etc/localtime:ro
    restart: always
    environment:
      - MYSQL_DATABASE=adamrms
      - MYSQL_ROOT_HOST=%
      - MYSQL_USER=userDocker
      - MYSQL_PASSWORD=passDocker
    env_file:
      - .env
    healthcheck:
      test:
        [
          "CMD",
          "mysqladmin",
          "ping",
          "-h",
          "localhost",
          "-u",
          "root",
          "-p$$MYSQL_ROOT_PASSWORD",
        ]
      timeout: 20s
      retries: 10
  adamrms:
    image: ghcr.io/adam-rms/adam-rms:latest
    container_name: adamrms
    restart: always
    depends_on:
      db:
        condition: service_healthy
    environment:
      - DB_HOSTNAME=db
      - DB_DATABASE=adamrms
      - DB_USERNAME=userDocker
      - DB_PASSWORD=passDocker
      - DB_PORT=3306
  # Remove this section to disable the cloudflared tunnel, and connect directly to the server
  cloudflared:
    image: cloudflare/cloudflared:2024.8.3
    container_name: cloudflare-tunnel
    restart: always
    command: tunnel run
    env_file:
      - .env
  # Remove this section to disable the datadog monitoring
  datadog:
    image: index.docker.io/datadog/agent:7
    restart: always
    pid: host
    environment:
      - DD_SITE=datadoghq.com
      - DD_LOGS_ENABLED=true
      - DD_LOGS_CONFIG_CONTAINER_COLLECT_ALL=true
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock
      - /proc/:/host/proc/:ro
      - /sys/fs/cgroup:/host/sys/fs/cgroup:ro
      - /var/lib/docker/containers:/var/lib/docker/containers:ro
    env_file:
      - .env
  mysql-backup:
    image: index.docker.io/databack/mysql-backup:1.0.0-rc5
    restart: unless-stopped
    container_name: mysql-backup
    command: dump
    environment:
      - DB_SERVER=db
      - DB_PORT=3306
      - DB_USER=userDocker
      - DB_PASS=passDocker
      - DB_NAMES=adamrms
      - NO_DATABASE_NAME=true
      - DB_DUMP_FREQUENCY=60 #Hourly
      - DB_DUMP_BEGIN=+2
      - COMPRESSION=gzip
      - DB_DUMP_SAFECHARS=true
      - NICE=true
    env_file:
      - .env
    depends_on:
      db:
        condition: service_healthy
    volumes:
      - /etc/localtime:/etc/localtime:ro
  watchtower:
    image: index.docker.io/containrrr/watchtower:1.7.1
    container_name: watchtower
    restart: always
    environment:
      - WATCHTOWER_CLEANUP=true
      - WATCHTOWER_POLL_INTERVAL=60
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock
volumes:
  db_data: {}
```

## Environment Variables

Place the following environment variables in a `.env` file in the same directory as the `docker-compose.yml` file.

```bash
# For the remote mysql access, set a root password
MYSQL_ROOT_PASSWORD=

# For databack/mysql-backup, set the following environment variables
# Bucket name for the backups e.g. "mysql-adamrms-backups"
DB_DUMP_TARGET=s3://bucketname
# AWS S3 key id
AWS_ACCESS_KEY_ID=
# AWS S3 secret key
AWS_SECRET_ACCESS_KEY=
# AWS S3 endpoint
AWS_ENDPOINT_URL=https://s3.eu-west-1.amazonaws.com
# AWS S3 region
AWS_DEFAULT_REGION=eu-west-1

# For the cloudflared tunnel, set the following environment variables
TUNNEL_TOKEN=abcdefghijklmnopqrstuvwxyz0123456789

# Datadog API key
DD_API_KEY=abcd

```
