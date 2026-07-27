# AdamRMS

[![GitHub release (latest by date)](https://img.shields.io/github/v/release/adam-rms/adam-rms)](https://github.com/adam-rms/adam-rms/releases)
[![GitHub](https://img.shields.io/github/license/adam-rms/adam-rms)](LICENSE)
[![GitHub issues](https://img.shields.io/github/issues/adam-rms/adam-rms)](https://github.com/adam-rms/adam-rms/issues)
[![GitHub stars](https://img.shields.io/github/stars/adam-rms/adam-rms)](https://github.com/adam-rms/adam-rms/stargazers)
[![GitHub contributors](https://img.shields.io/github/contributors/adam-rms/adam-rms)](https://github.com/adam-rms/adam-rms/graphs/contributors)

AdamRMS is a free, open source advanced Rental Management System for Theatre, AV & Broadcast. It helps rental businesses track assets, manage projects, handle client relationships, and streamline billing — all from a single web-based platform.

It is available as a [hosted solution](https://dash.adam-rms.com) or can be [self-hosted](https://adam-rms.com/self-hosting) using a pre-built Docker container.

🌐 **Website**: [adam-rms.com](https://adam-rms.com) · 📖 **Docs**: [User Guide](https://adam-rms.com/docs/v1) · 📊 **Usage Stats**: [Telemetry Dashboard](https://telemetry.bithell.studio/projects/adam-rms)

## Key Features

- **Asset Management** — Track equipment with barcode/QR code scanning support, categorisation, and maintenance scheduling
- **Project Management** — Plan and manage rental projects, assign assets, and track their dispatch status
- **Client Management** — Maintain client records and associate them with projects
- **Crew & Training** — Manage crew assignments and training records
- **Multi-tenancy** — Support for multiple independent business instances on a single deployment
- **CMS** — Built-in content management for custom dashboard pages
- **Search** — Global search across projects, clients, locations, users, and CMS pages
- **File Storage** — S3-compatible cloud storage for documents and images
- **Billing Integration** — Stripe integration for subscription management
- **Email Providers** — Pluggable email via SendGrid, Mailgun, Postmark, or SMTP
- **API** — RESTful JSON API with [OpenAPI documentation](https://adam-rms.com/api)

## Technology Stack

| Layer | Technology |
|---|---|
| **Backend** | PHP 8.3, Apache |
| **Templating** | Twig v3.7 |
| **Database** | MySQL 8.0 |
| **File Storage** | AWS S3 (or S3-compatible) |
| **Containerisation** | Docker (multi-arch: amd64, arm64) |
| **Dependency Management** | Composer |
| **Database Migrations** | Phinx |
| **Website & Docs** | Docusaurus v3, React |
| **CI/CD** | GitHub Actions |
| **Error Tracking** | Sentry |

## Project Structure

```
├── src/                    # Application source code
│   ├── api/                #   RESTful API endpoints (JSON)
│   ├── common/             #   Shared utilities & initialisation
│   │   ├── head.php        #     Public page bootstrap (config, DB, Twig)
│   │   ├── headSecure.php  #     Authenticated page bootstrap
│   │   └── libs/           #     Auth, Config, Email, Search, Telemetry
│   ├── assets/             #   Asset management module
│   ├── project/            #   Project management module
│   ├── clients/            #   Client management module
│   ├── maintenance/        #   Maintenance & service tracking
│   ├── instances/          #   Multi-tenant instance configuration
│   ├── login/              #   Authentication (including OAuth)
│   ├── server/             #   Server administration
│   └── ...                 #   CMS, training, locations, etc.
├── db/
│   ├── migrations/         #   Phinx database migrations
│   └── seeds/              #   Database seed data
├── website/                # Docusaurus website & documentation
│   ├── docs/               #   User guide, self-hosting, contributor docs
│   └── src/                #   React components & custom pages
├── .devcontainer/          # VS Code / Codespaces dev environment
├── Dockerfile              # Production Docker image definition
└── composer.json           # PHP dependencies
```

## Architecture Overview

AdamRMS follows a server-rendered architecture with a JSON API layer:

1. **Web Pages**: PHP controller → `headSecure.php` (auth & setup) → Twig template rendering
2. **API Endpoints**: PHP handler → `apiHeadSecure.php` (auth) → JSON response via `finish()`
3. **Multi-tenancy**: All data is scoped to an "instance" (business), and users can belong to multiple instances with different permissions in each
4. **Permissions**: Two-tier system — *server permissions* (global admin actions) and *instance permissions* (per-business actions)
5. **Database**: All entities use soft deletes (`*_deleted` flags) rather than hard deletes

## Self-Hosting with Docker

A maintained Docker image is published to GitHub Packages as [`adam-rms/adam-rms`](https://github.com/orgs/adam-rms/packages?repo_name=adam-rms), supporting both `linux/amd64` and `linux/arm64` platforms.

The container runs PHP 8.3 with Apache, exposes the application on port 80, and automatically runs database migrations on startup.

For full self-hosting instructions, including required environment variables and database setup, see the [self-hosting guide](https://adam-rms.com/self-hosting).

> **License note**: AdamRMS is licensed under AGPLv3, which means any changes you make to the source code must be kept open source.

## Contributing

[![Open in GitHub Codespaces](https://github.com/codespaces/badge.svg)](https://github.com/codespaces/new?ref=main&repo=217888995)

Contributions are very welcome! Please see the [contributing guide](https://adam-rms.com/contributing) for full details.

### Development Environment

This repo includes a pre-configured [devcontainer](https://code.visualstudio.com/docs/devcontainers/tutorial) that sets up everything you need:

| Service | Port | Purpose |
|---|---|---|
| PHP/Apache | 8080 | Application server |
| MySQL 8.0 | 3306 | Database |
| S3 Mock | 8081 | Local file storage emulation |
| phpMyAdmin | 8082 | Database admin UI |
| Mailpit | 8083 | Email testing (captures all outbound mail) |

**Getting started:**

1. **GitHub Codespaces** (recommended) — click the badge above to launch a ready-to-use cloud environment
2. **VS Code** — clone the repo, open it in VS Code, and use the "Reopen in Container" command

The devcontainer will automatically install dependencies, run database migrations, and seed test data.

## License

AdamRMS is licensed under the [GNU Affero General Public License v3.0](LICENSE).
