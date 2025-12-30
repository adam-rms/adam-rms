# AdamRMS - GitHub Copilot Instructions

## Project Overview

AdamRMS is an advanced Rental Management System for Theatre, AV & Broadcast. It provides comprehensive asset management, project tracking, client management, and billing capabilities for rental businesses in the entertainment industry.

## Technology Stack

### Backend
- **PHP 8.0+**: Object-oriented patterns with some procedural code
- **MySQL Database**: Via custom `adam-rms/mysqli-database-class` wrapper
- **Twig v3.7**: Templating engine for all views
- **Composer**: Dependency management

### Key Dependencies
- **Twig**: Template rendering (`twig/twig`, `twig/string-extra`)
- **Money Handling**: `moneyphp/money` for currency operations
- **Authentication**: `firebase/php-jwt`, `hybridauth/hybridauth`
- **Email**: `sendgrid/sendgrid`, `mailgun/mailgun-php`, `phpmailer/phpmailer`, `wildbit/postmark-php`
- **Cloud Services**: `aws/aws-sdk-php` for S3 file storage
- **PDF/Excel**: `phpoffice/phpspreadsheet` for exports
- **Error Tracking**: `sentry/sdk` for production error monitoring
- **Database Migrations**: `robmorgan/phinx`

### Infrastructure
- **Docker**: Containerized deployment with pre-built images
- **GitHub Actions**: CI/CD pipelines for Docker builds, API docs generation, and code review
- **Reviewdog**: Automated spelling and language checks on pull requests

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

### Request Flow

1. **Web Pages**: `*.php` controller → includes `headSecure.php` → sets `$PAGEDATA` → renders `*.twig` via `$TWIG`
2. **API Endpoints**: `api/**/*.php` → includes `apiHeadSecure.php` → processes request → calls `finish()` with JSON response
3. **Public Pages**: Login/signup pages use `head.php` instead of `headSecure.php`

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
- **Classes**: `PascalCase` (e.g., `assetAssignmentSelector`)
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
// Success response
finish(true, false, ["data" => $result]);

// Error response
finish(false, ["code" => "ERROR_CODE", "message" => "Human readable error"]);
```

API responses always include:
- `result`: boolean indicating success/failure
- `error`: array with `code` and `message` (if result is false)
- `response`: data payload (if result is true)

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

**IMPORTANT**: When adding new features that require permission checks, you MUST add the corresponding permissions to the definition files:

Permissions are defined in PHP files (not in the database) to avoid merge conflicts during development. This change was introduced in April 2023.

1. **For Instance Permissions**:
   Add a new entry to `src/common/libs/Auth/instanceActions.php`:
   
   ```php
   $instanceActions = [
       // ... existing permissions ...
       
       'CUSTOM:NEW_FEATURE:VIEW' => [
           'Category' => 'Custom',
           'Table' => 'New Feature',
           'Type' => 'View',
           'Detail' => null,
           'Combined Text Description' => 'Custom - New Feature: View',
           'Dependencies' => null,  // Or array of required permission keys
           'Comment' => null,
           'Supported Token Types' => ["web-session"],  // or ["web-session","app-v1"]
           'Caution' => null,  // Warning text if this is a dangerous permission
       ],
   ];
   ```

2. **For Server Permissions**:
   Add a new entry to `src/common/libs/Auth/serverActions.php`:
   
   ```php
   $serverActions = [
       // ... existing permissions ...
       
       'CUSTOM:NEW_FEATURE' => [
           'Category' => 'Custom',
           'Table' => 'Table Name',
           'Type' => 'Action Type',
           'Detail' => 'Additional details',
           'Dependencies' => null,  // Or array like ['USERS:VIEW', 'USERS:EDIT']
           'Comment' => null,
           'Supported Token Types' => ["web-session"],
       ],
   ];
   ```

3. **Use in Code**:
   ```php
   if (!$AUTH->instancePermissionCheck("CUSTOM:NEW_FEATURE:VIEW")) {
       die($TWIG->render('404.twig', $PAGEDATA));
   }
   ```

**Key Fields**:
- **Dependencies**: Array of permission keys that must also be granted
- **Supported Token Types**: Which authentication methods can use this permission. Unless you are developing for the mobile app, only enable "web-session".
  - `"web-session"`: Web browser sessions
  - `"app-v1"`: Mobile app v1
  - `"app-v2-magic-email"`: Mobile app v2 magic link auth
- **Caution**: Warning message for dangerous permissions (e.g., "Allows user to delete all data")

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

Common patterns for ordering:

```php
$DBLIB->orderBy("created_timestamp", "DESC");
$DBLIB->orderBy("name", "ASC");
```

### File Uploads

Files are stored in AWS S3

## Testing & Quality

- **GitHub Actions**: Automated workflows for Docker builds and API documentation
- **Reviewdog**: Automated spelling checks (UK English) and inclusive language review
- **Devcontainer**: VS Code/GitHub Codespaces configuration for consistent development environment
- **License**: AGPLv3 - all changes must remain open source

## Development Environment

- Use the provided `.devcontainer` for GitHub Codespaces or VS Code
- Development mode: Set environment variable `DEV_MODE=true`
- Database migrations: Use Phinx for schema changes
- Docker: Use provided Dockerfile and docker-compose setup

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
