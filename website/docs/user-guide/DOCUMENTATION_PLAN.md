# AdamRMS Documentation Improvement Plan

## Research Findings & Recommended Approach

This document outlines a comprehensive plan for dramatically improving the AdamRMS user guide documentation, based on analysis of the codebase, existing documentation, and best practices for AI-assisted documentation generation.

---

## 1. Current State Assessment

### What Exists Today
The project already has a Docusaurus v3 documentation site with:
- **21 user guide pages** across 6 sections (Intro, Assets, Business, Projects, CMS, Search, Training)
- **65+ tutorial screenshots** in `/website/static/img/tutorial/`
- **300+ API endpoints** auto-documented via OpenAPI annotations
- **Hosting/self-deployment guides** (9 pages)
- **Contributor documentation** (11 pages)

### Key Gaps Identified

| Area | Current State | Needed |
|------|--------------|--------|
| **Assets overview** | 2 sentences (8 lines) | Full walkthrough of asset types, individual assets, lifecycle |
| **Search** | 6 lines, just a bullet list | Step-by-step guide, search tips, filters |
| **Custom Dashboards** | 3 steps, minimal | Full setup guide, use cases, examples |
| **Dashboard/Home** | No page exists | Overview of calendar, widgets, navigation |
| **Ledger** | No page exists | Financial tracking, entries, reporting |
| **Locations** | No page exists | Creating, managing, assigning locations |
| **Clients** | No page exists | Client management, linking to projects |
| **Manufacturers** | No page exists | Manufacturer directory, linking to assets |
| **Calendar** | No page exists | Calendar views, exports, integrations |
| **User Account** | Brief mention in Getting Started | Full account management guide |
| **Notifications** | No page exists | Notification settings, types |
| **File Management** | No page exists | Uploading files to projects, assets |
| **Sub-projects** | Screenshots exist but minimal text | Full sub-project workflow |
| **Asset Dispatch** | Screenshots exist but brief coverage | Step-by-step dispatch workflow |
| **Invoicing/Quoting** | Screenshots exist but brief | Full financial document workflow |
| **Permissions Reference** | Listed per-page but no comprehensive guide | Full permission reference table |
| **Configuration Menu** | Only in hosting docs | Admin config guide with all 50+ options |
| **Public Sites** | Screenshot exists, no doc page | CMS public site setup guide |
| **Crew Recruitment** | Screenshots exist, brief coverage | Full recruitment workflow |

### Quality Issues in Existing Docs
- Many pages are stubs (under 20 lines)
- Inconsistent depth across sections (Projects is well-documented, Assets is sparse)
- Some screenshots exist without corresponding documentation text
- Missing "why" context - docs describe buttons but not workflows
- No troubleshooting or FAQ sections
- No glossary despite domain-specific terminology (instances, asset types, dispatch, etc.)

---

## 2. Automated Screenshot Generation

### Can Claude Code Spin Up the App?

**Yes, with caveats.** The project has a complete Docker development environment:

```
.devcontainer/
├── Dockerfile          # PHP 8.3 dev container
├── docker-compose.yml  # App + MySQL + S3 mock + phpMyAdmin + Mailpit
└── devcontainer.json   # Port forwarding, post-create commands
```

**The setup includes:**
- MySQL database with Phinx migrations (auto-seeds on start)
- S3 mock for file storage
- Mailpit for email testing
- PHP built-in server on port 8080

**To spin up a working instance:**
```bash
cd .devcontainer
docker compose up -d
# Wait for services
docker compose exec app composer update
docker compose exec app php vendor/bin/phinx migrate
docker compose exec app php vendor/bin/phinx seed:run
docker compose exec app php -S 0.0.0.0:8080 -t /workspace/src
```

### Screenshot Automation Approach

**Recommended: Playwright with scripted workflows**

```bash
# Install Playwright
npm init -y && npx playwright install chromium

# Take screenshots via CLI
npx playwright screenshot --browser chromium http://localhost:8080/login screenshot-login.png
```

**For authenticated pages, a Playwright script is needed:**
```javascript
// screenshot-tool.js
const { chromium } = require('playwright');

async function captureScreenshots() {
  const browser = await chromium.launch();
  const page = await browser.newPage();

  // Login
  await page.goto('http://localhost:8080');
  await page.fill('#email', 'admin@test.com');
  await page.fill('#password', 'password');
  await page.click('#login');

  // Capture each page
  const pages = [
    { url: '/dashboard', name: 'dashboard' },
    { url: '/assets', name: 'assets-list' },
    { url: '/projects', name: 'projects-list' },
    // ... etc
  ];

  for (const p of pages) {
    await page.goto(`http://localhost:8080${p.url}`);
    await page.waitForLoadState('networkidle');
    await page.screenshot({
      path: `website/static/img/tutorial/${p.name}.png`,
      fullPage: true
    });
  }

  await browser.close();
}
```

### Practical Limitations
1. **Seed data quality** - Screenshots are only as good as the test data. The Phinx seeds need to create realistic-looking data (named projects, diverse assets, sample clients).
2. **Authentication flow** - Need to script login; Google OAuth won't work in automated testing.
3. **State-dependent views** - Some pages look different based on permissions, data presence, or project status. Multiple screenshots may be needed per feature.
4. **Environment constraints** - Docker and Playwright both need to be available in the CI/build environment. This works in GitHub Codespaces and most CI runners.
5. **Screenshot freshness** - Screenshots become outdated as UI changes. Consider making the screenshot script part of CI so they auto-update.

### Recommendation
Create a `docs/screenshots/` directory with a Playwright script that:
1. Spins up the app via Docker
2. Seeds comprehensive test data
3. Captures screenshots for every documented page
4. Outputs to `website/static/img/tutorial/`

This script can be run manually or in CI to keep screenshots fresh.

---

## 3. Documentation Structure & Templates

### Recommended Page Template

Every feature page should follow this consistent structure:

```markdown
---
sidebar_position: N
title: Feature Name
---

# Feature Name

Brief 1-2 sentence description of what this feature does and why you'd use it.

:::note Permissions Required
CATEGORY:ACTION:DETAIL
:::

![Feature Overview Screenshot](/img/tutorial/section/feature-overview.png)
*Caption describing the screenshot*

## Overview

2-3 paragraphs explaining:
- What problem this feature solves
- How it fits into the broader AdamRMS workflow
- Key concepts/terminology

## Getting Started

Step-by-step guide for first-time users:

1. Navigate to **Menu > Feature Name**
2. Click **Create New**
3. Fill in the required fields:
   - **Field Name** - Description of what to enter
   - **Field Name** - Description of what to enter
4. Click **Save**

![Step Screenshot](/img/tutorial/section/feature-step.png)

## Common Tasks

### Task Name
Step-by-step instructions for the most common operations.

### Another Task
...

## Advanced Usage

Features that power users need, including:
- Bulk operations
- Import/export
- Integration with other features
- Customization options

## Tips & Best Practices

:::tip
Practical advice based on real-world usage patterns.
:::

:::caution
Common pitfalls or things that could go wrong.
:::

## Related Features

- [Related Feature 1](link) - How it connects
- [Related Feature 2](link) - How it connects

## Permissions Reference

| Permission | Description |
|-----------|-------------|
| `CATEGORY:ACTION` | What this permission allows |
```

### Proposed Site Structure

```
User Guide/
├── Introduction
├── Getting Started
├── Dashboard & Navigation
│   ├── Dashboard Overview
│   ├── Calendar
│   └── Navigation & Menu
├── Assets
│   ├── Understanding Assets (overview + concepts)
│   ├── Creating Asset Types
│   ├── Managing Individual Assets
│   ├── Finding Assets (Search & Filters)
│   ├── Asset Groups
│   ├── Asset Barcodes
│   ├── Maintenance Jobs
│   └── Asset Import
├── Projects
│   ├── Project Overview
│   ├── Creating & Managing Projects
│   ├── Project Assets (Assigning & Dispatch)
│   ├── Crew Management
│   ├── Crew Recruitment
│   ├── Sub-Projects
│   ├── Project Finance
│   ├── Quotes & Invoices
│   └── Project Files & Audit Log
├── Business Management
│   ├── Business Settings
│   ├── User Management & Roles
│   ├── Permissions (Comprehensive Reference)
│   ├── Clients
│   ├── Locations
│   ├── Financial Ledger
│   ├── Calendar & Scheduling
│   ├── Statistics & Reporting
│   └── Business Utilities
├── CMS & Customization
│   ├── CMS Pages
│   ├── Public Sites
│   └── Custom Dashboards
├── Account & Settings
│   ├── Your Account
│   ├── Notifications
│   ├── Calendar Export
│   └── Search
├── Training
│   └── Training Module
├── Configuration Reference
│   └── All Configuration Options
├── Glossary
└── FAQ / Troubleshooting
```

This expands from **21 pages to ~35+ pages**, with every existing page getting significantly more content.

---

## 4. Code-Informed Documentation Strategy

### Using the Codebase to Write Accurate Docs

The AdamRMS codebase is a goldmine for documentation because of its clear architecture:

#### a) Permission Documentation from Source
File: `src/common/libs/Auth/instanceActions.php`
- Contains every permission with Category, Type, Detail, Dependencies, and Caution text
- Can generate a complete permissions reference table automatically

#### b) Configuration Options from Source
File: `src/common/libs/Config/configStructureArray.php`
- Contains 50+ config options with descriptions, types, defaults, and validation rules
- Can generate a complete configuration reference automatically

#### c) Page Features from Twig Templates
Each `.twig` file reveals:
- What data is displayed on each page
- What actions/buttons are available
- What forms exist and what fields they contain
- Conditional rendering based on permissions (shows what different user roles see)

#### d) API Endpoints from OpenAPI Annotations
The 300+ API endpoints (in `src/api/`) have `@OA` annotations describing:
- Parameters and their types
- Request/response formats
- Error codes

#### e) Database Schema from Migrations
The Phinx migrations (in `db/migrations/`) reveal:
- All database entities and their relationships
- What data is tracked for each feature
- Historical evolution of features

### Practical Workflow for Code-Informed Docs

For each feature page:
1. **Read the `.php` controller** to understand what data is loaded and what permissions are checked
2. **Read the `.twig` template** to understand what the user sees and what actions are available
3. **Read the relevant `api/` endpoints** to understand what operations are possible
4. **Read the migration files** to understand the data model
5. **Cross-reference with `instanceActions.php`** to document all relevant permissions
6. **Write documentation** that accurately reflects what the code does, including edge cases

---

## 5. Recommended Implementation Approach

### Phase 1: Foundation (Do First)
1. **Create the documentation template** (as shown above)
2. **Set up the screenshot automation script** with Playwright
3. **Create seed data** that populates realistic-looking test data
4. **Generate the Glossary** from codebase terminology
5. **Generate the Permissions Reference** from `instanceActions.php`
6. **Generate the Configuration Reference** from `configStructureArray.php`

### Phase 2: Expand Existing Pages
Work through each existing page and expand it to match the template:
1. Assets overview (currently 2 sentences → full guide)
2. Search (currently a bullet list → full guide)
3. Custom Dashboards (currently 3 steps → full guide)
4. Each project sub-page (add more workflow context)

### Phase 3: Create Missing Pages
Create new pages for undocumented features:
1. Dashboard & Navigation
2. Clients
3. Locations
4. Ledger
5. Manufacturers
6. Calendar
7. User Account management
8. Notifications
9. Public Sites
10. FAQ / Troubleshooting

### Phase 4: Screenshots & Polish
1. Run the screenshot automation to capture fresh screenshots
2. Add screenshots to all new and updated pages
3. Cross-link related features
4. Review for consistency and completeness

### Working Method for Claude Code

**Feature-by-feature is strongly recommended over all-at-once because:**
- Each page can be carefully informed by reading the relevant source files
- Easier to review and iterate on quality
- Prevents context window overflow
- Allows checking each page builds correctly

**Per-feature workflow:**
1. Read the controller (`.php`), template (`.twig`), and API files for the feature
2. Read the existing doc page (if any)
3. Write/expand the documentation following the template
4. Reference specific permissions from `instanceActions.php`
5. Include relevant screenshots (existing or note where new ones needed)
6. Cross-link to related features

**Consistency tools:**
- The standardized template ensures structural consistency
- A style guide section in the contributor docs can define:
  - Tone (friendly but professional, second person "you")
  - British English spelling (matching the project's reviewdog config)
  - How to format permissions, screenshots, tips, and warnings
  - Standard terminology from the glossary

---

## 6. Quick Wins (Can Be Done Immediately)

These improvements can be made right now without screenshots:

1. **Expand the Assets overview page** (currently 2 sentences)
2. **Expand the Search page** (currently a bullet list)
3. **Create a Glossary** from codebase terminology
4. **Generate a Permissions Reference** from `instanceActions.php`
5. **Generate a Configuration Reference** from `configStructureArray.php`
6. **Create a Dashboard overview page**
7. **Create a Clients page**
8. **Create a Locations page**
9. **Add a FAQ/Troubleshooting page**
10. **Improve cross-linking** between all existing pages

---

## Summary

| Approach | Feasibility | Impact | Effort |
|----------|------------|--------|--------|
| Expand existing thin pages | ✅ Immediate | High | Low-Medium |
| Create missing feature pages | ✅ Immediate | High | Medium |
| Auto-generate reference pages from code | ✅ Immediate | High | Low |
| Screenshot automation via Docker+Playwright | ✅ Feasible | Medium | Medium-High |
| Feature-by-feature Claude Code workflow | ✅ Recommended | Highest | Medium per page |
| Glossary & FAQ | ✅ Immediate | Medium | Low |
