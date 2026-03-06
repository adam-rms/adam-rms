#!/usr/bin/env bash
# Creates GitHub issues for the security and technical debt items identified in the architecture review.
# Usage: GH_TOKEN=your_token ./create-issues.sh
# Or:    ./create-issues.sh your_token

set -euo pipefail

REPO="adam-rms/adam-rms"
TOKEN="${GH_TOKEN:-${1:-}}"

if [[ -z "$TOKEN" ]]; then
  echo "Usage: GH_TOKEN=<token> $0" >&2
  echo "   or: $0 <token>" >&2
  exit 1
fi

create_issue() {
  local title="$1"
  local body="$2"
  local labels="$3"

  response=$(curl -s -w "\n%{http_code}" -X POST \
    "https://api.github.com/repos/${REPO}/issues" \
    -H "Authorization: Bearer ${TOKEN}" \
    -H "Accept: application/vnd.github+json" \
    -H "X-GitHub-Api-Version: 2022-11-28" \
    -H "Content-Type: application/json" \
    -d "$(jq -n \
      --arg title "$title" \
      --arg body "$body" \
      --argjson labels "$(echo "$labels" | jq -R 'split(",")')" \
      '{title: $title, body: $body, labels: $labels}')")

  http_code=$(echo "$response" | tail -1)
  body_out=$(echo "$response" | head -n -1)

  if [[ "$http_code" == "201" ]]; then
    url=$(echo "$body_out" | jq -r '.html_url')
    echo "Created: $url"
  else
    echo "Failed ($http_code): $(echo "$body_out" | jq -r '.message // "unknown error"')" >&2
  fi
  sleep 0.5  # avoid secondary rate limits
}

echo "Creating GitHub issues for adam-rms/adam-rms..."
echo ""

# ── CRITICAL ────────────────────────────────────────────────────────────────

create_issue \
  "Replace custom password hashing with password_hash()/password_verify()" \
  "## Summary

The application uses a custom password hashing scheme instead of PHP's built-in \`password_hash()\`/\`password_verify()\` functions.

**Affected files**
- \`src/api/login/login.php\` – password verification on login
- \`src/api/account/changePass.php\` – password hashing on change

**Current behaviour**

\`\`\`php
// login.php
\$user['users_password'] != hash(\$user['users_hash'], \$user['users_salty1'] . \$password . \$user['users_salty2'])

// changePass.php
hash(\$CONFIG['AUTH_NEXTHASH'], \$AUTH->data['users_salty1'] . \$_POST['newpass'] . \$AUTH->data['users_salty2'])
\`\`\`

The hash algorithm is stored per-user in \`users_hash\` (e.g. \`sha256\`, \`md5\`). Regardless of algorithm, a single \`hash()\` call is not suitable for passwords because it is fast by design, making offline brute-force attacks orders of magnitude cheaper than with bcrypt/Argon2.

## Required changes

1. **Migrate \`changePass.php\`** to store new passwords with \`password_hash(\$_POST['newpass'], PASSWORD_BCRYPT)\` (or \`PASSWORD_ARGON2ID\`).
2. **Migrate \`login.php\`** to:
   - First try \`password_verify(\$password, \$user['users_password'])\`.
   - If that fails, fall back to the legacy hash comparison (for accounts not yet migrated).
   - On successful legacy login, immediately rehash and update the stored value with \`password_hash()\`. This provides a transparent, gradual migration.
3. **Schema**: The \`users_hash\` and \`users_salty1\`/\`users_salty2\` columns become redundant once all passwords are migrated. Add a follow-up migration to remove them after a suitable deprecation window.
4. **Register/invite flows**: Audit any other place a password is first set (e.g. registration, admin reset) and apply the same change.
5. Add a test that verifies the new login and change-password flows end-to-end.

## Acceptance criteria
- New passwords are stored with \`password_hash()\`.
- Existing users can still log in and are silently migrated on next login.
- No \`hash()\` call is used anywhere for password storage or verification.
" \
  "security,critical"

# ── HIGH ─────────────────────────────────────────────────────────────────────

create_issue \
  "Add CSRF protection to API endpoints" \
  "## Summary

None of the API endpoints under \`src/api/\` validate a CSRF token. Any authenticated user who visits a malicious page could have state-mutating requests submitted on their behalf.

**Affected file**: \`src/api/apiHead.php\` (included by every API endpoint)

## Background

The API is consumed by:
1. The web frontend (same-origin AJAX via \`ajaxcall()\` in \`template.twig\`)
2. The mobile app (sends a JWT \`Authorization\` header)

Because the mobile app uses a bearer token it is not vulnerable to CSRF. However browser-based sessions (cookies) are.

## Recommended approach: Double-submit cookie

1. On session creation / first load, generate a cryptographically random token and store it in:
   - The PHP session (\`\$_SESSION['csrf_token']\`)
   - A \`SameSite=Strict\` cookie (readable by JavaScript)
2. The frontend \`ajaxcall()\` helper (defined in \`template.twig\`) should read the cookie and include the value in a custom header (e.g. \`X-CSRF-Token\`) on every AJAX request.
3. In \`src/api/apiHeadSecure.php\`, for non-GET requests that do **not** carry a JWT \`Authorization\` header, assert that \`\$_SERVER['HTTP_X_CSRF_TOKEN'] === \$_SESSION['csrf_token']\`. Reject with 403 otherwise.

## Alternative: SameSite cookies only

Setting \`session.cookie_samesite = Strict\` in PHP config provides a strong baseline for modern browsers and may be sufficient as a first step, but the explicit token check is more robust.

## Acceptance criteria
- All state-mutating API calls from the web frontend include and validate a CSRF token.
- JWT-authenticated calls (mobile app) are unaffected.
- An unauthenticated cross-origin form POST to any \`apiHeadSecure.php\`-protected endpoint returns 403.
" \
  "security,high"

create_issue \
  "Redact sensitive fields from audit log payloads" \
  "## Summary

The \`auditLog()\` function in \`src/common/libs/bCMS/bCMS.php\` records the \`\$revelantData\` parameter verbatim. Call sites that pass raw request data (including password change events) may write plaintext passwords to the \`auditLog\` table.

**Affected file**: \`src/common/libs/bCMS/bCMS.php\` – \`auditLog()\` method

**Example at risk**: \`changePass.php\` calls \`auditLog(\"UPDATE\", \"users\", \"PASSWORD CHANGE\", ...)\` — the string is safe, but other callers may pass \`\$_POST\` arrays or similar.

## Required changes

1. Audit all call sites of \`auditLog()\` across the codebase and identify any that pass arrays or strings that may include password fields (\`password\`, \`pass\`, \`newpass\`, \`oldpass\`, etc.).
2. Add a sanitisation step inside \`auditLog()\` itself: if \`\$revelantData\` is an array, strip any key whose name matches a denylist of sensitive field names before serialising:
   \`\`\`php
   private const SENSITIVE_KEYS = ['password', 'pass', 'newpass', 'oldpass', 'token', 'secret', 'key'];
   \`\`\`
3. Alternatively, if \`\$revelantData\` is always a plain string at call sites, document this contract explicitly and add a type hint.
4. Consider whether existing rows in \`auditLog\` need to be scrubbed (depends on findings from step 1).

## Acceptance criteria
- No password or secret value appears in the \`auditLog\` table under any normal application flow.
- The function is documented with a note about what should and should not be passed as \`\$revelantData\`.
" \
  "security,high"

create_issue \
  "Replace wildcard CORS with an explicit origin allowlist" \
  "## Summary

\`src/api/apiHead.php\` sets \`Access-Control-Allow-Origin: *\` unconditionally. This permits any origin on the internet to make credentialled requests to the API from a user's browser.

\`\`\`php
// apiHead.php line 6
header(\"Access-Control-Allow-Origin: *\");
\`\`\`

## Impact

A wildcard CORS policy combined with cookie-based authentication means that a script on any domain can issue API requests that execute in the context of a logged-in user. This amplifies the impact of any XSS vulnerability elsewhere and weakens CSRF mitigations.

Note: browsers do NOT send cookies with wildcard-CORS responses unless \`withCredentials\` is set, but the policy is still overly permissive for a multi-tenant SaaS.

## Required changes

1. Define the allowed origins. Likely candidates:
   - The configured \`ROOTURL\` for the instance
   - Any known mobile app origin (or none — the mobile app uses JWTs and typically doesn't need CORS)
2. Replace the static header with dynamic origin validation:
   \`\`\`php
   \$allowed = [rtrim(\$CONFIG['ROOTURL'], '/')];
   \$origin = \$_SERVER['HTTP_ORIGIN'] ?? '';
   if (in_array(\$origin, \$allowed, true)) {
       header('Access-Control-Allow-Origin: ' . \$origin);
       header('Vary: Origin');
   }
   \`\`\`
3. Remove \`Access-Control-Allow-Origin: *\` entirely.

## Acceptance criteria
- Cross-origin requests from an unlisted origin receive no \`Access-Control-Allow-Origin\` header.
- Requests from the configured instance origin succeed normally.
- The mobile app is unaffected (it doesn't rely on CORS).
" \
  "security,high"

# ── MEDIUM ───────────────────────────────────────────────────────────────────

create_issue \
  "Upgrade firebase/php-jwt from ^5.2 to ^6.x" \
  "## Summary

\`composer.json\` pins \`firebase/php-jwt\` to \`^5.2\`. Version 6.x is the current major release and contains improved defaults and security fixes.

**File**: \`composer.json\`

## Changes in v6

- Stricter algorithm validation (algorithm must be explicitly specified; prevents algorithm confusion attacks)
- Improved clock-skew handling
- PSR-4 autoloading improvements

## Migration steps

1. Update \`composer.json\`: \`\"firebase/php-jwt\": \"^6.0\"\`
2. Run \`composer update firebase/php-jwt\`
3. The v6 API has breaking changes — key handling moved to typed key objects:
   \`\`\`php
   // v5
   JWT::decode(\$token, \$key, ['HS256']);
   // v6
   use Firebase\\JWT\\Key;
   JWT::decode(\$token, new Key(\$key, 'HS256'));
   \`\`\`
4. Search the codebase for \`JWT::decode\` and \`JWT::encode\` calls and update them.
   Key files to check: \`src/common/libs/Auth/main.php\`
5. Run the application and verify mobile app JWT authentication still works.

## Acceptance criteria
- \`composer.json\` requires \`^6.0\`
- All JWT encode/decode calls use the v6 API
- Mobile app login continues to function
" \
  "security,dependencies"

create_issue \
  "Fix XSS risk: sanitizeString() uses ENT_NOQUOTES, leaving single quotes unescaped" \
  "## Summary

\`bCMS::sanitizeString()\` calls \`htmlspecialchars(\$var, ENT_NOQUOTES)\`. The \`ENT_NOQUOTES\` flag means **neither single nor double quotes are escaped**. If a sanitised value is rendered inside an HTML attribute delimited by single quotes, an attacker can inject arbitrary attributes or close the tag.

**File**: \`src/common/libs/bCMS/bCMS.php\`

\`\`\`php
function sanitizeString(\$var) {
    \$var = htmlspecialchars(\$var, ENT_NOQUOTES);  // single quotes not escaped
    return \$var;
}
\`\`\`

## Risk

If any Twig template or PHP file renders a sanitised value inside a single-quoted attribute:
\`\`\`html
<input value='\${sanitized}'>
\`\`\`
An input of \`foo' onmouseover='alert(1)\` would render unescaped.

## Fix

Change \`ENT_NOQUOTES\` to \`ENT_QUOTES\` (escapes both) or \`ENT_QUOTES | ENT_SUBSTITUTE\` (also replaces invalid UTF-8):

\`\`\`php
\$var = htmlspecialchars(\$var, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
\`\`\`

## Before changing, audit call sites

1. Search for all uses of \`sanitizeString()\` across the codebase.
2. Identify any call site where the result is placed in a context (Twig template, PHP echo) that uses single-quote HTML attribute delimiters.
3. Twig auto-escaping may already handle this in templates — confirm the escaping strategy per context.
4. Note: \`sanitizeStringMYSQL()\` wraps this function; ensure DB query usage is also safe.

## Acceptance criteria
- \`sanitizeString()\` uses \`ENT_QUOTES\` (or \`ENT_QUOTES | ENT_SUBSTITUTE\`).
- No regression in existing functionality from the change.
" \
  "security,medium"

create_issue \
  "Tighten Content Security Policy: remove 'unsafe-inline' and 'unsafe-eval'" \
  "## Summary

The CSP defined in \`src/common/head.php\` includes \`'unsafe-inline'\` and \`'unsafe-eval'\` for the \`script-src\` directive, and \`'unsafe-inline'\` for \`style-src\`. These directives significantly weaken the CSP because they allow execution of any inline script/style, nullifying protection against XSS injection.

\`\`\`php
// head.php
[\"value\" => \"'unsafe-inline'\", \"comment\" => \"We have loads of inline JS\"],
[\"value\" => \"'unsafe-eval'\", \"comment\" => \"\"],
\`\`\`

## Why it exists

Inline JavaScript is embedded throughout Twig templates (event handlers, page-specific logic) and AdminLTE uses inline styles.

## Remediation path

This is a multi-step effort:

1. **Inventory all inline scripts**: Find every \`<script>\` block and \`on*\` attribute in \`.twig\` files.
2. **Extract to external files**: Move inline JS to \`src/static-assets/js/\` files and load them via \`<script src=\"...\"\>\`.
3. **Event handlers**: Replace \`onclick=\"...\"\` with \`addEventListener\` calls from external files, or use data attributes.
4. **Nonce-based approach (interim)**: Before full extraction, generate a per-request nonce in \`head.php\` and inject it into \`<script nonce=\"...\">\` tags. Use the nonce in the CSP instead of \`'unsafe-inline'\`. This is a significant improvement even before all inline scripts are removed.
5. **Remove \`'unsafe-eval'\`**: Audit for any \`eval()\`, \`new Function()\`, or \`setTimeout(string)\` uses and replace them.
6. Once inline scripts are eliminated, remove both \`'unsafe-inline'\` and \`'unsafe-eval'\` from the CSP.

## Acceptance criteria
- The CSP \`script-src\` directive does not include \`'unsafe-inline'\` or \`'unsafe-eval'\`.
- The application functions correctly with the tightened policy.
- Browser console shows no CSP violation errors under normal usage.
" \
  "security,medium"

create_issue \
  "Migrate database collation from latin1_swedish_ci to utf8mb4_unicode_ci" \
  "## Summary

The database schema defaults to \`latin1_swedish_ci\` collation. This does not support Unicode characters beyond the Latin-1 range, meaning emoji, extended characters, and many non-Latin scripts will either be corrupted or rejected.

**Affected**: \`db/schema.php\`, existing Phinx migrations

## Impact

- User-supplied text containing emoji or non-Latin characters may be silently truncated or cause query errors.
- Multi-tenant SaaS used internationally should fully support Unicode.
- The March 2025 migration (\`20250302170000\`) began addressing this but a full migration covering all tables and columns is needed.

## Required changes

1. **Audit** all existing tables and columns: \`SHOW FULL COLUMNS FROM \<table\>\` to find anything still on \`latin1\`.
2. **Write a Phinx migration** that ALTERs each affected table and column to \`utf8mb4\` charset with \`utf8mb4_unicode_ci\` collation. Example:
   \`\`\`sql
   ALTER TABLE users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   \`\`\`
3. **Update \`db/schema.php\`** defaults so new tables are created with \`utf8mb4\`.
4. **Update \`docker-compose\`/MySQL config** to set \`character-set-server=utf8mb4\` and \`collation-server=utf8mb4_unicode_ci\` by default.
5. **Test** that existing data round-trips correctly after the migration — watch for any columns that store binary data that should not be charset-converted.

## Acceptance criteria
- All user-facing string columns use \`utf8mb4_unicode_ci\`.
- Emoji and extended Unicode characters can be stored and retrieved without corruption.
- The migration runs cleanly on a fresh install and on a database with existing data.
" \
  "database,medium"

create_issue \
  "Add composer audit to CI for dependency vulnerability scanning" \
  "## Summary

The CI pipeline has no step to check PHP dependencies for known security vulnerabilities. \`composer audit\` (available since Composer 2.4) queries the PHP Security Advisories Database and exits non-zero if any installed package has a known CVE.

**Affected file**: \`.github/workflows/\` (no existing workflow covers this)

## Required changes

Add a new workflow (or a job in an existing workflow) that runs on every push and PR:

\`\`\`yaml
name: Dependency Security Audit
on: [push, pull_request]

jobs:
  audit:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
      - run: composer install --no-dev --no-interaction --prefer-dist
      - run: composer audit
\`\`\`

Consider also:
- Running \`composer audit\` on a schedule (e.g. weekly) to catch new advisories against unpinned dependencies.
- Adding \`--format=json\` and uploading results as a GitHub Security Advisory / SARIF artifact.

## Acceptance criteria
- \`composer audit\` runs in CI on every PR and push.
- A PR introducing a dependency with a known CVE causes CI to fail.
" \
  "ci,security,dependencies"

create_issue \
  "Align PHP version across dev, CI, and production environments" \
  "## Summary

The PHP version used differs across environments:

| Environment | PHP Version |
|-------------|------------|
| Production Dockerfile | 8.3 |
| Devcontainer | 8.1 |
| \`generateApiDocs.yaml\` CI workflow | 8.1 |

This mismatch means bugs that only manifest on PHP 8.2+ or 8.3 may not be caught in development or CI, and API docs may be generated from a different runtime than production uses.

## Required changes

1. **Devcontainer** (\`/.devcontainer/docker-compose.yml\` or \`Dockerfile\`): change the PHP image from \`8.1\` to \`8.3-apache\` (to match production).
2. **\`generateApiDocs.yaml\`**: update the \`setup-php\` step to use \`php-version: '8.3'\`.
3. Run the devcontainer and confirm the app starts correctly on 8.3.
4. Run the API docs generation workflow and confirm it produces the same output.

## Acceptance criteria
- All three environments use PHP 8.3.
- The devcontainer builds and starts without errors.
- The \`generateApiDocs.yaml\` workflow passes.
" \
  "dx,ci,medium"

create_issue \
  "Add PHPStan or Psalm static analysis to CI" \
  "## Summary

There is no static analysis step in CI. Adding PHPStan or Psalm would catch type errors, undefined variables, unreachable code, and other bugs before they reach production — without requiring a full test suite.

## Recommended approach

**PHPStan** is widely used in the PHP ecosystem and integrates easily with GitHub Actions.

1. Install as a dev dependency:
   \`\`\`
   composer require --dev phpstan/phpstan
   \`\`\`
2. Create a \`phpstan.neon\` config at the repo root. Start at level 1 (lowest strictness) to avoid an overwhelming number of errors:
   \`\`\`neon
   parameters:
     level: 1
     paths:
       - src
   \`\`\`
3. Add a GitHub Actions job:
   \`\`\`yaml
   - run: vendor/bin/phpstan analyse
   \`\`\`
4. Fix reported errors iteratively, increasing the level over time toward level 8.

**Alternative**: Psalm (\`vimeo/psalm\`) follows the same pattern with \`psalm.xml\` config.

## Acceptance criteria
- \`phpstan analyse\` (or equivalent) runs in CI and passes on the current codebase at the chosen baseline level.
- Future PRs must not introduce new PHPStan errors.
" \
  "ci,quality,medium"

create_issue \
  "Add API versioning strategy" \
  "## Summary

The API under \`src/api/\` has no versioning. Both the web frontend and the mobile app consume the same endpoints. This means any breaking change to an endpoint risks breaking the mobile app for users who have not yet updated.

## Current state

All endpoints are at the root path, e.g. \`/api/login/login.php\`. There is no \`/api/v1/\` prefix or negotiation mechanism.

## Recommended approach

1. **Namespace new endpoints** under \`/api/v2/\` (or similar). Existing endpoints remain as \`/api/v1/\` (their current location) without changes.
2. The mobile app can target \`/api/v2/\` for new features while existing installs continue using \`/api/v1/\`.
3. Document the versioning strategy and deprecation policy.

**Alternative**: Header-based versioning via \`Accept: application/vnd.adam-rms.v2+json\` — but path-based is simpler with the current PHP file-per-endpoint pattern.

## Minimum viable first step

Add a \`/api/v1/\` directory (or Apache rewrite rule) that aliases the current endpoints, so the mobile app can explicitly opt in to \`v1\`. This doesn't change existing behaviour but establishes the pattern.

## Acceptance criteria
- A versioning strategy is documented (even if just in an ADR or README section).
- New breaking changes to the API are introduced under a new version path.
- The existing endpoints continue to work without changes.
" \
  "api,architecture"

create_issue \
  "Upgrade jQuery from 3.4.1 to latest 3.x and remove jQuery Migrate" \
  "## Summary

The application loads jQuery 3.4.1 (released April 2019) from a CDN. jQuery 3.7.x is the current release in the 3.x series and includes security and bug fixes. jQuery Migrate 1.2.1 (for jQuery 1.x compatibility) is also loaded but is unlikely to be necessary.

**File**: \`src/assets/template.twig\`

## Required changes

1. Update the jQuery CDN URL and SRI hash in \`template.twig\` to the latest 3.x release (check https://releases.jquery.com/ for the current version and its SRI hash).
2. Update the Bootstrap JS bundle CDN URL/hash if needed (Bootstrap 4.4.1 includes its own jQuery requirement — verify compatibility).
3. Remove or update the jQuery Migrate script — it is for migrating code written for jQuery 1.x; if the codebase uses modern jQuery APIs this is unnecessary overhead.
4. Test the UI thoroughly after the update, paying particular attention to:
   - Date range pickers
   - Select2 dropdowns
   - Barcode scanning (zxing-js)
   - AJAX calls via \`ajaxcall()\`

## Acceptance criteria
- jQuery 3.7.x (or latest 3.x) is loaded with a correct SRI hash.
- jQuery Migrate is either removed or justified with a comment.
- No JavaScript console errors on the main UI pages.
" \
  "frontend,dependencies"

create_issue \
  "Add Dockerfile HEALTHCHECK instruction" \
  "## Summary

The production Dockerfile has no \`HEALTHCHECK\` instruction. Container orchestrators (Docker Swarm, Kubernetes, ECS) use health checks to determine when a container is ready to receive traffic and when to restart an unhealthy one.

**File**: \`Dockerfile\`

## Required change

Add a \`HEALTHCHECK\` instruction that verifies Apache is serving requests:

\`\`\`dockerfile
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \\
  CMD curl -f http://localhost/ || exit 1
\`\`\`

\`--start-period=60s\` gives Phinx time to run migrations before health checks begin.

Alternatively, use a lightweight PHP ping endpoint (e.g. \`/health\`) that also checks the database connection.

## Acceptance criteria
- The Dockerfile has a \`HEALTHCHECK\` instruction.
- \`docker inspect <container>\` shows the health status as \`healthy\` after startup.
- An unhealthy container (e.g. Apache stopped) is marked \`unhealthy\`.
" \
  "infrastructure,dx"

create_issue \
  "Switch Docker layer cache from type=local to type=gha in GitHub Actions" \
  "## Summary

The \`dockerBuild.yml\` workflow uses \`type=local\` for Docker BuildKit layer caching. This stores cache on the runner's local filesystem, which is ephemeral — the cache is lost between workflow runs, providing no benefit. \`type=gha\` uses GitHub Actions Cache, which persists across runs.

**File**: \`.github/workflows/dockerBuild.yml\`

## Required change

Replace the cache configuration:

\`\`\`yaml
# Before
- name: Build and push
  uses: docker/build-push-action@v5
  with:
    cache-from: type=local,src=/tmp/.buildx-cache
    cache-to: type=local,dest=/tmp/.buildx-cache-new,mode=max

# After
- name: Build and push
  uses: docker/build-push-action@v5
  with:
    cache-from: type=gha
    cache-to: type=gha,mode=max
\`\`\`

Also remove any steps that move or manage the local cache directory.

## Acceptance criteria
- \`dockerBuild.yml\` uses \`type=gha\` for both \`cache-from\` and \`cache-to\`.
- A second workflow run after the first shows a cache hit (faster build time).
" \
  "ci,infrastructure"

create_issue \
  "Tag a versioned release of adam-rms/mysqli-database-class instead of pinning to dev-main" \
  "## Summary

\`composer.json\` pins the database abstraction library to \`dev-main\`:

\`\`\`json
\"adam-rms/mysqli-database-class\": \"dev-main\"
\`\`\`

While this is a first-party fork (same organisation), \`dev-main\` means any commit pushed to that repo is immediately pulled in by \`composer update\`. This makes change tracking difficult and could introduce regressions without a visible version bump.

## Required changes

1. Tag a versioned release in the \`adam-rms/mysqli-database-class\` repository (e.g. \`v2.0.0\`) that matches the current \`main\` branch.
2. Update \`composer.json\` in this repo to require the tagged version: \`\"^2.0.0\"\`.
3. Going forward, changes to \`mysqli-database-class\` should be tagged with a new version before being pulled in here.
4. Update \`dependabot.yml\` to include \`adam-rms/mysqli-database-class\` in automated dependency updates (currently it may be excluded because it uses \`dev-main\`).

## Acceptance criteria
- \`composer.json\` references a semver tag, not \`dev-main\`.
- \`composer.lock\` resolves to the correct tagged commit.
- Future changes to \`mysqli-database-class\` require a deliberate \`composer update\` to adopt.
" \
  "dependencies,architecture"

echo ""
echo "Done. All issues created."
