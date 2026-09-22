#!/bin/bash
# Prepares Claude Code on the web sessions to run the e2e suite (see AGENTS.md → Testing).
set -euo pipefail

if [ "${CLAUDE_CODE_REMOTE:-}" != "true" ]; then
  exit 0
fi

cd "$CLAUDE_PROJECT_DIR"

# PHP 8.3 to match production (Twig 3.7 breaks on 8.4), and MySQL 8
if ! command -v php8.3 >/dev/null || ! command -v mysqld >/dev/null; then
  apt-get update -q >/dev/null
  DEBIAN_FRONTEND=noninteractive apt-get install -y -q \
    php8.3-cli php8.3-mysql php8.3-intl php8.3-zip php8.3-gd php8.3-mbstring php8.3-curl php8.3-xml \
    mysql-server >/dev/null
fi

service mysql start >/dev/null
for _ in $(seq 30); do mysqladmin ping >/dev/null 2>&1 && break; sleep 1; done
# Same database and credentials as .devcontainer/docker-compose.yml
mysql -e "CREATE DATABASE IF NOT EXISTS db;
  CREATE USER IF NOT EXISTS 'user'@'localhost' IDENTIFIED BY 'pass';
  GRANT ALL ON db.* TO 'user'@'localhost';"

php8.3 "$(command -v composer)" install --no-interaction --quiet
(cd e2e && npm install --no-audit --no-fund --silent)

echo 'export PHP_BINARY=php8.3' >> "$CLAUDE_ENV_FILE"
