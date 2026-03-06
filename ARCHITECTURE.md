# AdamRMS Architecture Review

This document provides a comprehensive review of the AdamRMS codebase architecture, covering both the current state and areas for potential improvement.

## Table of Contents

1. [System Overview](#system-overview)
2. [Technology Stack](#technology-stack)
3. [Backend Architecture](#backend-architecture)
4. [Frontend Architecture](#frontend-architecture)
5. [Database Layer](#database-layer)
6. [Authentication & Authorisation](#authentication--authorisation)
7. [API Design](#api-design)
8. [Email System](#email-system)
9. [File Storage](#file-storage)
10. [Deployment & Infrastructure](#deployment--infrastructure)
11. [CI/CD Pipeline](#cicd-pipeline)
12. [Security Analysis](#security-analysis)
13. [Technical Debt & Improvement Areas](#technical-debt--improvement-areas)

---

## System Overview

AdamRMS is a multi-tenant, server-side rendered PHP web application for rental management in Theatre, AV & Broadcast. It is licensed under AGPL-3.0 and maintained by Bithell Studios Ltd.

**Key characteristics:**
- Multi-tenant SaaS architecture (called "instances")
- Server-side rendering via Twig templates
- Hybrid AJAX for partial page updates
- PHP 8.3 + MySQL 8.0 + Apache
- Companion mobile app supported via JWT authentication
- 301 PHP files, 128 Twig templates, 50 database migrations

---

## Technology Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| Language | PHP | ^8.0 (runtime: 8.3) |
| Web Server | Apache2 | (via php:8.3-apache image) |
| Database | MySQL | 8.0 |
| Templating | Twig | v3.7.x |
| DB Abstraction | adam-rms/mysqli-database-class | dev-main (custom fork) |
| Migrations | Phinx | ^0.13.4 |
| Frontend UI | AdminLTE | 3.x (Bootstrap 4.4.1) |
| JavaScript | jQuery | 3.4.1 |
| CSS | AdminLTE (pre-compiled) | 1.3MB minified |
| Containerisation | Docker | Multi-stage build |
| Error Tracking | Sentry | 3.6.x |
| Payments | Stripe | ^14.5 |
| Auth (mobile) | Firebase JWT | ^5.2 |

---

## Backend Architecture

### Request Lifecycle

1. **Entry Point**: Apache routes requests to PHP files in `src/`
2. **Bootstrap**: Every page includes `src/common/head.php` which sets up:
   - Composer autoload
   - Database connection (`MysqliDb`)
   - Twig environment with caching
   - Config loading from DB
   - Content Security Policy headers
   - Session initialisation
   - Auth (`bID` class)
3. **Secured Pages**: Include `src/common/headSecure.php` which:
   - Validates authentication
   - Loads user/instance data
   - Records analytics events
   - Enforces terms acceptance, email verification, password change
4. **Page Logic**: Each `page.php` queries the DB and populates `$PAGEDATA`
5. **Rendering**: `$TWIG->render('page.twig', $PAGEDATA)` outputs HTML

### Directory Structure

```
src/
├── common/
│   ├── head.php              # Bootstrap: DB, Twig, Config, CSP, Auth
│   ├── headSecure.php        # Auth enforcement + instance/user data loading
│   └── libs/
│       ├── Auth/             # bID class: session tokens + JWT (mobile)
│       ├── Config/           # DB-backed config system
│       ├── Email/            # Multi-provider email abstraction
│       ├── Search/           # Search functionality
│       ├── Telemetry/        # Analytics/telemetry
│       ├── bCMS/             # Utility functions (sanitisation, formatting, etc.)
│       └── twigExtensions.php # Custom Twig filters
├── api/                      # REST-like API endpoints (26 directories)
│   ├── apiHead.php           # API bootstrap (JSON headers, CORS, auth)
│   ├── apiHeadSecure.php     # Secured API bootstrap
│   └── {resource}/           # Resource-specific endpoints
├── assets/                   # Twig base template + modals + widgets
├── static-assets/            # CSS, JS, images (no build pipeline)
└── [128 page.php + page.twig pairs]
```

### Key Classes and Patterns

| Class/File | Purpose |
|------------|---------|
| `bID` (Auth/main.php) | Authentication: session tokens, JWT (mobile app), admin impersonation |
| `bCMS` (bCMS/bCMS.php) | Global utilities: string sanitisation, HTML purification, formatting |
| `Config` (Config/Config.php) | DB-backed config with in-memory cache per request |
| `EmailHandler` | Multi-provider email abstraction (Mailgun, Postmark, SMTP, SendGrid) |
| `assetAssignmentSelector` | Business logic for asset assignment cascading (in apiHead.php) |

### Global Variables Pattern

The codebase relies heavily on PHP globals, which is a legacy pattern:
- `$DBLIB` — database connection (accessed via `global $DBLIB` in functions)
- `$AUTH` / `$GLOBALS['AUTH']` — current user auth object
- `$TWIG` — Twig environment
- `$CONFIG` — configuration array
- `$PAGEDATA` — data passed to Twig templates
- `$bCMS` — utility class instance

---

## Frontend Architecture

### Templating

Twig v3.7 is used for server-side HTML rendering with a block inheritance pattern:

```twig
{% extends "assets/template.twig" %}
{% block content %}
  <!-- page-specific HTML -->
{% endblock %}
```

The base template (`src/assets/template.twig`, ~40KB) includes all shared navigation, CSS, and JavaScript.

### JavaScript

- **No build pipeline** — no webpack, Vite, or bundler
- **No TypeScript** — plain JavaScript only
- **No npm/yarn** — no frontend package manager
- jQuery 3.4.1 with jQuery Migrate for compatibility
- AdminLTE 3.x JavaScript (~44KB minified)
- Inline JavaScript functions defined in `template.twig`:
  - `ajaxcall(path, data, callback, reloadIfError)` — wrapper for jQuery AJAX to API endpoints
  - `pad()`, `insertURLQueryParam()`, etc.
- Third-party libraries vendored into `src/static-assets/libs/`:
  - `sweetalert2` — modal dialogs
  - `select2-bootstrap4` — enhanced selects
  - `zxing-js` — barcode scanning
  - `gridstrap` — grid layout
  - `pace` — page load progress
  - `invert-color` — colour utilities

### CSS

- AdminLTE monolithic CSS: `adminlte.min.css` (1.3MB minified)
- Bootstrap 4.4.1 via CDN (cdnjs.cloudflare.com)
- Google Fonts via CDN
- No CSS preprocessor; no CSS modules
- Dark/light mode toggle via jQuery DOM class manipulation, persisted to `localStorage`

### External CDN Dependencies

The application loads assets from external CDNs at runtime (no Subresource Integrity verification):
- cdnjs.cloudflare.com (Bootstrap, jQuery, Font Awesome, etc.)
- fonts.googleapis.com / fonts.gstatic.com

---

## Database Layer

### Abstraction

The project uses a custom fork of `mysqli-database-class` (`adam-rms/mysqli-database-class`, pinned to `dev-main`). This is a query builder, not a full ORM — there are no model classes. Database calls are made directly in PHP page files and API endpoints.

**Risks:**
- Pinning to `dev-main` means the dependency can change unexpectedly without a version bump
- No ORM means schema changes require manual updates across all query sites
- Raw `MysqliDb` calls scattered throughout 301 PHP files

### Migrations

Phinx is used for database schema migrations. There are 50 migrations spanning from 2019 to 2025, demonstrating active schema evolution. Migrations are run via `migrate.sh` on container start.

### Schema

The base schema is defined in `db/schema.php`. Key entities include:
- `instances` — multi-tenant organisation units
- `users` / `authTokens` — authentication
- `assets` / `assetTypes` / `assetsBarcodes` — equipment inventory
- `projects` / `projectsStatuses` / `projectsTypes` — project management
- `clients` — client management
- `maintenanceJobs` — maintenance tracking
- `analyticsEvents` — internal usage analytics
- `config` — per-instance configuration stored in DB

---

## Authentication & Authorisation

### Authentication Methods

1. **Web sessions**: PHP sessions storing a token, validated against `authTokens` table (12-hour expiry)
2. **JWT (mobile app)**: HS256 JWTs signed with `AUTH_JWTKey` config value; decoded token extracted to look up session
3. **Magic links**: Email-based login (`app-v2-magic-email` JWT type)

### Authorisation Model

- **Instance-level**: Users belong to instances via position/role assignments
- **Server-level**: Server administrators have a separate permission set (`serverActions.php`)
- **Instance positions**: Users have positions within instances that grant permissions
- **`AUTH->serverPermissionCheck()`**: Used throughout for server-admin gating

### Security Concerns

- JWT library `firebase/php-jwt` is pinned to `^5.2` — version 5.x is old (current is 6.x with improved defaults)
- `$GLOBALS['bCMS']->sanitizeString()` uses `htmlspecialchars` with `ENT_NOQUOTES` which doesn't escape single quotes — this is a potential XSS vector in contexts where values appear in HTML attributes with single quotes
- The API sets `Access-Control-Allow-Origin: *` (wildcard CORS) unconditionally

---

## API Design

### Structure

The API lives under `src/api/` with 26 resource directories. Each endpoint is a standalone PHP file that includes `apiHead.php` or `apiHeadSecure.php`.

**Request handling quirks** (from `apiHead.php`):
```php
// JSON body is decoded and merged into both $_GET and $_POST for "compatibility"
// GET params are also copied into $_POST
// $_POST is the "authoritative copy"
```
This means GET params, POST form data, and JSON bodies are all merged — a pattern designed for backwards compatibility with the mobile app.

### Response Format

```json
{ "result": true/false, "response": { ... } }
{ "result": false, "error": { "code": null, "message": "..." } }
```

The `finish()` function (defined in `apiHead.php`) calls `die()` to terminate execution.

### Versioning

There is no API versioning. This makes breaking changes risky as both the web frontend and mobile app consume the same endpoints.

---

## Email System

The email system provides a multi-provider abstraction in `src/common/libs/Email/`:

| Provider | Handler |
|----------|---------|
| SendGrid | `SendgridHandler.php` |
| Mailgun | `MailgunHandler.php` |
| Postmark | `PostmarkHandler.php` |
| SMTP (PHPMailer) | `SMTPHandler.php` |
| Generic | `EmailHandler.php` |

Provider selection is driven by instance configuration. This is a good separation of concerns.

---

## File Storage

- **AWS S3** (`aws/aws-sdk-php ^3.173`) for file/asset storage
- **CloudFront** for CDN delivery of S3 assets
- Local S3 emulation via Adobe S3Mock in development (via devcontainer)
- Twig `|s3URL` filter for generating signed URLs

---

## Deployment & Infrastructure

### Docker

Multi-stage Dockerfile:
1. **`deps` stage** (composer:lts): Installs PHP dependencies, no dev packages
2. **`final` stage** (php:8.3-apache): Runtime image with PHP extensions, Apache config, app files

**PHP configuration overrides:**
- `post_max_size=64M`
- `upload_max_filesize=64M`
- `memory_limit=256M`
- `max_execution_time=600`

The container entrypoint is `migrate.sh`, which runs Phinx migrations on startup before Apache starts.

### Development Environment

The devcontainer (`/.devcontainer/`) orchestrates 5 services via Docker Compose:
1. PHP 8.1 Apache (main app — note: dev uses 8.1, production uses 8.3)
2. MySQL 8.0
3. Adobe S3Mock (local S3 emulation)
4. phpMyAdmin (port 8082)
5. Mailpit (email testing, port 8083)

**Note:** The devcontainer uses PHP 8.1 while the production Dockerfile uses PHP 8.3. This version mismatch could hide compatibility issues during development.

### Multi-Platform Support

Docker images are built for: `linux/amd64`, `linux/arm64`, `linux/arm64/v8`

---

## CI/CD Pipeline

### Workflows

| Workflow | Trigger | Purpose |
|----------|---------|---------|
| `dockerBuild.yml` | GitHub Release published | Build & push multi-platform Docker image to GHCR |
| `reviewdog.yml` | Pull Request | Spelling (Misspell, UK locale) + inclusive language (Alex) checks |

### Gaps

- No automated testing in CI (no PHPUnit, no frontend tests)
- No static analysis (no PHPStan, Psalm, or similar)
- No dependency vulnerability scanning (no `composer audit` step)
- No linting for PHP code style (no PHP CS Fixer or PHP CodeSniffer)
- Docker layer cache uses `type=local` (file-based) — less efficient than `type=gha` (GitHub Actions cache)

---

## Security Analysis

### Strengths

- Content Security Policy headers are implemented and structured (`head.php`)
- HTML purification via `ezyang/htmlpurifier` for user-generated content
- Sentry error tracking for production
- Session-based auth with 12-hour token expiry
- Multi-tenancy with strict instance ID scoping on DB queries

### Weaknesses & Risks

| Issue | Location | Severity |
|-------|----------|----------|
| Wildcard CORS (`Access-Control-Allow-Origin: *`) | `apiHead.php:7` | Medium |
| `firebase/php-jwt ^5.2` is outdated (current: 6.x) | `composer.json` | Medium |
| `sanitizeString()` uses `ENT_NOQUOTES` — single quotes not escaped | `bCMS.php` | Medium |
| No SRI (Subresource Integrity) on CDN-loaded assets | `template.twig` | Medium |
| `'unsafe-inline'` and `'unsafe-eval'` in CSP for scripts | `head.php` | Medium |
| Dev PHP version (8.1) differs from prod (8.3) | devcontainer vs Dockerfile | Low |
| DB abstraction dependency pinned to `dev-main` | `composer.json` | Medium |
| No automated dependency vulnerability scanning | CI pipeline | Medium |
| API request body merged into `$_GET`/`$_POST` | `apiHead.php:10-18` | Low |

---

## Technical Debt & Improvement Areas

### High Priority

1. **Add automated testing**: No test suite exists. PHPUnit for unit/integration tests would significantly improve confidence in changes.
2. **API versioning**: The API has no versioning strategy. Both the web UI and mobile app share endpoints — versioning would allow safe evolution.
3. **Unpin `dev-main` dependency**: `adam-rms/mysqli-database-class` is pinned to `dev-main`, creating a risk of unexpected breaking changes. It should be tagged and versioned.
4. **Upgrade `firebase/php-jwt`**: Version 5.x is outdated; upgrade to 6.x for improved security defaults.
5. **PHP version alignment**: Align the devcontainer PHP version (8.1) with production (8.3).

### Medium Priority

6. **Static analysis**: Add PHPStan or Psalm to the CI pipeline to catch type errors and logic bugs before runtime.
7. **Dependency auditing**: Add `composer audit` to CI to automatically detect known vulnerabilities in dependencies.
8. **CORS policy**: Replace the wildcard `Access-Control-Allow-Origin: *` with an explicit allowlist of trusted origins.
9. **CSP tightening**: The `'unsafe-inline'` and `'unsafe-eval'` CSP directives significantly weaken the Content Security Policy. Moving JavaScript out of Twig templates would allow these to be removed.
10. **Reduce global variable usage**: The heavy reliance on PHP globals (`$DBLIB`, `$AUTH`, `$CONFIG`, etc.) makes testing and reasoning about code difficult. Dependency injection or a service container would improve this.

### Lower Priority

11. **Frontend modernisation**: No asset pipeline means all 1.3MB of CSS is loaded on every page. A build step (even a simple one) could enable code splitting and cache busting.
12. **jQuery version**: jQuery 3.4.1 (2019) is in active security maintenance; upgrade to 3.7.x.
13. **Remove jQuery Migrate**: If modern jQuery is used throughout, the migrate plugin is unnecessary overhead.
14. **SRI for CDN assets**: Add Subresource Integrity hashes to CDN-loaded scripts and stylesheets.
15. **Docker cache improvement**: Switch from `type=local` to `type=gha` for Docker layer caching in GitHub Actions.
16. **PHP CS Fixer**: Add a code style linter to CI for consistent PHP formatting.

---

*Review date: March 2026*
