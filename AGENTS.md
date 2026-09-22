# AdamRMS - Agent Instructions

Instructions for AI coding agents (Claude Code, GitHub Copilot, etc.) working in this repository.

## Project Overview

AdamRMS is an advanced Rental Management System for Theatre, AV & Broadcast. It provides comprehensive asset management, project tracking, client management, and billing capabilities for rental businesses in the entertainment industry.

## Project Status: Maintenance Mode

This codebase is in maintenance mode while a rewrite is developed. Changes here should be:

- **Bug fixes, security fixes and small quality-of-life improvements only.** Larger feature requests belong in the rewrite — say so rather than implementing them here.
- **Minimal and surgical.** Don't refactor, reformat or "improve" code that isn't part of the fix. Match the surrounding style.
- **Tested.** Every bug fix should come with an e2e test that fails before the fix and passes after it (see Testing below).
- **One issue or one module per PR**, so each change is easy to review.

Take extra care with, and call out in the PR description, any change that touches authentication, billing/Stripe, instance scoping (multi-tenancy) or database migrations.

## Technology Stack

### Backend

- **PHP 8.3** (production runtime): Object-oriented patterns with some procedural code. Do not run on PHP 8.4 — Twig 3.7 compiles closure names that 8.4 formats differently, producing a parse error on logged-in pages
- **MySQL Database**: Via custom `adam-rms/mysqli-database-class` wrapper
- **Twig v3.7**: Templating engine for all views
- **Composer**: Dependency management

### Frontend (CDN-loaded, no build step)

- **jQuery 3.4.1**, **Bootstrap 4.4.1** — loaded globally in `src/assets/template.twig`
- **Select2 4.0.13**, **SweetAlert2 8.19.1**, **Bootbox.js 5.3.4** — loaded globally
- **Moment.js 2.24.0**, **DaterangePicker 3.0.5** — loaded globally
- **DataTables 1.10.21**, **FullCalendar 5.11.5** — loaded per-page via `htmlIncludes` block
- All loaded from CDN with SRI integrity hashes

### Key Dependencies

- **Twig**: Template rendering (`twig/twig`, `twig/string-extra`)
- **Money Handling**: `moneyphp/money` for currency operations
- **Authentication**: `firebase/php-jwt`, `hybridauth/hybridauth`
- **Email**: `sendgrid/sendgrid`, `mailgun/mailgun-php`, `phpmailer/phpmailer`, `wildbit/postmark-php`
- **Cloud Services**: `aws/aws-sdk-php` for S3 file storage
- **PDF/Excel**: `phpoffice/phpspreadsheet` for exports
- **Error Tracking**: `sentry/sdk` for production error monitoring
- **Database Migrations**: `robmorgan/phinx`

### Website & Documentation

- **Docusaurus v3**: Static site generator for the project website and documentation
- **React**: Custom components for the website (pricing table, homepage features)
- **Cloudflare Workers**: Website hosting and deployment

### Infrastructure

- **Docker**: Containerized deployment with pre-built images
- **GitHub Actions**: CI/CD pipelines for Docker builds, API docs generation, and documentation sync

## Architecture

### Directory Structure

- `src/` - Main application code
  - `*.php` - Page controllers
  - `*.twig` - Twig template views
  - `api/` - RESTful API endpoints (JSON responses)
  - `common/` - Shared utilities and initialization
    - `head.php` - Basic initialization (config, database, Twig)
    - `headSecure.php` - Authentication-required initialization
    - `libs/` - Utility classes and helper functions
  - `assets/`, `clients/`, `instances/`, `login/`, `maintenance/`, `project/`, etc. - Feature modules
- `db/migrations/` - Phinx database migrations
- `website/` - Docusaurus website and documentation
  - `docs/` - Documentation source files (user guide, hosting, contributor guides)
  - `src/` - React components and custom pages
  - `static/` - Static assets (images, redirects, headers)
  - `docusaurus.config.ts` - Docusaurus configuration (TypeScript)

### Request Flow

1. **Web Pages**: `*.php` controller → includes `headSecure.php` → sets `$PAGEDATA` → renders `*.twig` via `$TWIG`
2. **API Endpoints**: `api/**/*.php` → includes `apiHeadSecure.php` → processes request → calls `finish()` with JSON response
3. **Public Pages**: Login/signup pages use `head.php` instead of `headSecure.php`

### Twig Template Structure

All pages extend the base template `assets/template.twig`. Two main blocks:

- `htmlIncludes` - Additional CSS/JS for the page
- `content` - Main page content

```twig
{% extends "assets/template.twig" %}
{% block content %}
    <!-- page content -->
{% endblock %}
```

Reusable components use `{% embed %}` with context passing. Sub-templates go in `twigIncludes/` subdirectories.

## Global Variables

The codebase uses several global variables throughout:

- `$DBLIB` - Database connection object (mysqli-database-class wrapper)
- `$AUTH` - Authentication/authorization object with user data and permission checks
- `$TWIG` - Twig template engine instance
- `$CONFIG` - Application configuration array
- `$PAGEDATA` - Array passed to Twig templates containing all view data
- `$bCMS` - CMS/business logic helper instance
- `$CONFIGCLASS` - Configuration management class

## Coding Conventions

### Naming Conventions

- **Database Columns**: `snake_case` with table prefix (e.g., `projects_id`, `users_userid`, `assetTypes_name`, `instances_deleted`)
- **PHP Variables**: `camelCase` for local variables, `UPPERCASE` for constants
- **Classes**: Inconsistent - some `camelCase` (e.g., `assetAssignmentSelector`, `bCMS`), some `PascalCase` (e.g., `Config`, `AuthFail`). Match surrounding code style.
- **Functions**: `camelCase`

### Database Patterns

Use `$DBLIB` methods for all database operations:

```php
// SELECT with joins and conditions
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects_deleted", 0);
$DBLIB->join("clients", "projects.clients_id=clients.clients_id", "LEFT");
$projects = $DBLIB->get("projects", null, ["projects_id", "projects_name", "clients_name"]);

// INSERT
$DBLIB->insert("tableName", ["column1" => $value1, "column2" => $value2]);

// UPDATE
$DBLIB->where("id", $id);
$DBLIB->update("tableName", ["column" => $newValue]);
```

### API Response Format

All API endpoints must use the `finish()` function:

```php
finish(true, false, ["data" => $result]);  // Success
finish(false, ["code" => "ERROR_CODE", "message" => "Human readable error"]);  // Error
```

### Input Validation

- `$bCMS->sanitizeString($var)` — HTML escaping via `htmlspecialchars()` (for display)
- `$bCMS->sanitizeStringMYSQL($var)` — SQL-safe escaping via `$DBLIB->escape()` (for LIKE clauses)
- Always check POST parameters exist before use:

```php
if (!isset($_POST['projects_id'])) finish(false, ["code" => "PARAM-ERROR", "message" => "No data for action"]);

// Whitelist allowed fields for updates
$array = array_intersect_key($data, array_flip(["users_username", "users_name1", "users_email"]));
```

### Authentication & Authorization

AdamRMS uses a two-tier permission system: **Server Permissions** and **Instance Permissions**.

#### Permission System Overview

- **Server Permissions**: Global permissions that apply across all instances (e.g., `USERS:EDIT`, `INSTANCES:CREATE`, `VIEW-AUDIT-LOG`)
  - Checked via `$AUTH->serverPermissionCheck("PERMISSION:NAME")`
  - Granted through user positions (roles) in the `positions` and `positionsGroups` tables
  - Examples: Creating instances, viewing all users, server configuration
- **Instance Permissions**: Permissions specific to a single instance/business (e.g., `ASSETS:CREATE`, `PROJECTS:VIEW`, `BUSINESS:USERS:VIEW:LIST`)
  - Checked via `$AUTH->instancePermissionCheck("PERMISSION:NAME")`
  - Granted through instance positions in the `instancePositions` table
  - Users can have different permissions in different instances
  - Examples: Creating assets, viewing projects, managing clients

#### Permission Checking Patterns

```php
// Check login status first
if (!$AUTH->login) die($TWIG->render('404.twig', $PAGEDATA));

// Check server permission
if (!$AUTH->serverPermissionCheck("USERS:EDIT")) die($TWIG->render('404.twig', $PAGEDATA));

// Check instance permission
if (!$AUTH->instancePermissionCheck("ASSETS:CREATE")) die($TWIG->render('404.twig', $PAGEDATA));

// Multiple permission check (OR logic)
if ($AUTH->instancePermissionCheck("BUSINESS:USERS:VIEW:INDIVIDUAL_USER") or $AUTH->serverPermissionCheck("USERS:EDIT")) {
    // User has either permission
}

// API endpoints - use finish() for errors
if (!$AUTH->instancePermissionCheck("PROJECTS:EDIT")) finish(false, ["code" => "AUTH-ERROR", "message" => "No auth for action"]);
```

#### How Permissions Are Loaded

Permissions are loaded in the `Auth` class constructor (`src/common/libs/Auth/main.php`):

1. **Server Permissions**: Retrieved from user's positions → position groups → actions
   - Stored in `$AUTH->data['positions']`
   - Available as array in `$this->serverPermissions`
2. **Instance Permissions**: Retrieved per instance from `instancePositions` and `userInstances`
   - Each instance in `$AUTH->data['instances']` has a `permissions` array
   - Current instance accessible via `$AUTH->data['instance']['permissions']`
   - Users can have extra permissions via `userInstances_extraPermissions`

#### Permission Definitions

- **Server Permissions**: Defined in `src/common/libs/Auth/serverActions.php`
- **Instance Permissions**: Defined in `src/common/libs/Auth/instanceActions.php`

Both files export arrays mapping permission keys to metadata including supported token types.

#### Creating New Permissions

When adding features requiring permission checks, add entries to the definition files.

Permissions are defined in PHP files (not in the database) to avoid merge conflicts.

**Instance permission** — add to `src/common/libs/Auth/instanceActions.php`:

```php
'CUSTOM:NEW_FEATURE:VIEW' => [
    'Category' => 'Custom', 'Table' => 'New Feature', 'Type' => 'View',
    'Detail' => null, 'Combined Text Description' => 'Custom - New Feature: View',
    'Dependencies' => null, 'Comment' => null,
    'Supported Token Types' => ["web-session"], 'Caution' => null,
],
```

**Server permission** — add to `src/common/libs/Auth/serverActions.php` with the same structure (minus `Combined Text Description` and `Caution`).

**Key fields**: `Dependencies` = array of required permission keys; `Supported Token Types` = `["web-session"]` for web-only (add `"app-v1"` for mobile app support).

#### Permission Naming Conventions

Follow hierarchical naming with colons:

- `CATEGORY:SUBCATEGORY:ACTION[:DETAIL]`
- Examples:
  - `ASSETS:CREATE`
  - `ASSETS:ASSET_TYPES:EDIT`
  - `BUSINESS:USERS:VIEW:LIST`
  - `PROJECTS:PROJECT_ASSETS:CREATE:ASSIGN_AND_UNASSIGN`
  - `MAINTENANCE_JOBS:EDIT:USER_ASSIGNED_TO_JOB`

#### Multi-Instance Considerations

- **Always Check Instance Scoping**: Users can belong to multiple instances
- **Filter Queries**: Always filter by `$AUTH->data['instance']['instances_id']`
- **Instance IDs**: Available in `$AUTH->data['instance_ids']` array
- **Super Administrators**: Users with `INSTANCES:FULL_PERMISSIONS_IN_INSTANCE` have all instance permissions

### Security Best Practices

- Always use parameterized queries via `$DBLIB` methods (never concatenate SQL)
- Validate and sanitize user input
- Check soft-delete flags: `table_deleted = 0`
- Verify instance ownership for all operations
- Use proper CORS headers in API endpoints
- Set cache control headers appropriately
- Track analytics events for important actions

### Money Handling

Use the `moneyphp/money` library for all currency operations:

```php
use Money\Currency;
use Money\Money;

$amount = new Money($valueInCents, new Currency($AUTH->data['instance']['instances_config_currency']));
$formatted = apiMoney($amount); // Returns formatted currency string
```

### Error Handling

- **Development**: `DEV_MODE=true` shows detailed errors
- **Production**: Errors are logged to Sentry, minimal details exposed to users
- **API Errors**: Always return structured error responses via `finish()`

### Headers

- **API Endpoints**: Include proper headers for CORS, JSON content-type, cache control
- **Web Pages**: Standard HTML5 with proper charset and viewport meta tags

## Common Patterns

### Soft Deletes

All major entities use soft deletes with a `*_deleted` flag:

```php
$DBLIB->where("projects_deleted", 0);
$DBLIB->where("assets_deleted", 0);
```

### Multi-tenancy (Instances)

AdamRMS is multi-tenant. Always scope queries to the current instance:

```php
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
```

### Pagination & Sorting

API pagination uses `$DBLIB->paginate()`:

```php
$DBLIB->pageLimit = 20;
$page = isset($_POST['page']) ? $bCMS->sanitizeString($_POST['page']) : 1;
$results = $DBLIB->arraybuilder()->paginate('tableName', $page, ["fields"]);
finish(true, null, ["data" => $results, "pagination" => ["page" => $page, "total" => $DBLIB->totalPages]]);
```

### File Uploads

Files are stored in AWS S3

### Database Migrations

Use Phinx with the `change()` method for reversible migrations. File naming: `YYYYMMDDHHmmss_description.php`.

```php
<?php
declare(strict_types=1);
use Phinx\Migration\AbstractMigration;

final class AddFeatureColumn extends AbstractMigration
{
    public function change(): void
    {
        $this->table('tableName')
            ->addColumn('columnName', 'string', ['limit' => 255, 'null' => true])
            ->update();
    }
}
```

### Configuration

- **Environment variables**: `DB_HOSTNAME`, `DB_USERNAME`, `DB_PASSWORD`, `DB_DATABASE`, `DB_PORT`, `DEV_MODE`
- **Database config**: Runtime config stored in `config` table (`config_key`/`config_value`), loaded via `Config` class in `src/common/libs/Config/Config.php`
- Config structure defined in `src/common/libs/Config/configStructureArray.php`

## Testing & Quality

- **E2E tests**: Playwright (TypeScript) in `e2e/`, run on every PR by `.github/workflows/e2e-tests.yml`. There are no unit tests.
- **GitHub Actions**: E2E tests (`e2e-tests.yml`), Docker builds (`dockerBuild.yml`), API docs generation (`generateApiDocs.yaml`), and documentation sync (`syncDocsToAISearch.yml`)

### Running the E2E tests

Tests run against PHP's built-in server (started by Playwright) and a real MySQL 8 database. Prerequisites: PHP 8.3 with the Dockerfile's extensions, `composer install`, and MySQL reachable with the devcontainer credentials (`user`/`pass`, database `db` on `127.0.0.1:3306` — override with `DB_*` env vars). Claude Code on the web sets all of this up via `.claude/hooks/session-start.sh`.

```bash
cd e2e
npm install
npx playwright install chromium   # not needed where a browser is pre-installed
npm test                          # set PHP_BINARY=php8.3 if `php` on your PATH isn't 8.3
```

`e2e/globalSetup.ts` migrates and seeds the database, then `e2e/setup/seed.php` writes the config the first-run setup form would ask for and makes sure the test super admin `test@example.com` / `password!` exists (resetting its password if it has been changed), so the suite also works against a used devcontainer database. The server runs with `DEV_MODE=true` (as the devcontainer does), so pages that require login show the auth error and a login link instead of redirecting.

### Writing E2E tests

- `e2e/public/` — tests that don't need a session. `e2e/authenticated/` — import `test` from `e2e/fixtures.ts` to get a `page` already logged in as the super admin.
- Write characterisation tests: assert what the app does today. If you find a bug while writing tests, mark the test `test.fixme` with a comment and raise an issue rather than fixing it in the same PR.
- For a bug fix, add a test that reproduces the bug first, then fix it.
- Tests share one database and run serially; create the data each test needs rather than relying on what an earlier test left behind.
- **OpenAPI docs**: Auto-generated from `@OA\` annotations in PHP files via `zircote/swagger-php`
- **License**: AGPLv3 - all changes must remain open source

## Development Environment

- Use the provided `.devcontainer` for GitHub Codespaces or VS Code
- Default login after seeding: username `username` / password `password!`
- Development mode: Set environment variable `DEV_MODE=true`
- Database migrations: Run via `php vendor/bin/phinx migrate` (config in `phinx.php`)
- Docker: Use provided Dockerfile and docker-compose setup
- **UK English**: Use UK spelling in user-facing strings (e.g., "organisation" not "organization")

## Important Constraints

- **Backward Compatibility**: Maintain API compatibility for mobile apps
- **Multi-instance Support**: Never assume single-tenancy
- **Soft Deletes**: Never hard-delete records; use `*_deleted` flags
- **License Compliance**: All code must be AGPLv3 compatible

## When Reviewing Code

Please check for:

- Proper authentication and authorization checks
- SQL injection prevention (use $DBLIB parameterized methods)
- Checks of instance permissions for a given user to perform an operation
- Instance scoping on all queries
- Soft delete flag checks
- Proper error handling with structured responses
- License compatibility of any suggested dependencies
- Consistent naming conventions (especially database columns)
- Multi-instance considerations
