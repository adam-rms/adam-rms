This directory contains the public marketing site for the AdamRMS Rental Management System, a [Docusaurus 2](https://docusaurus.io/) site.

It also serves as the documentation for the site, and its API. It is community maintained and written, and covered by the same licence as the main source code. It is included in the same repo such that changes in the API and features can be included in the same PR. 

# Writing Content

## Documentation 

### Create a new version

Release a version 1.0 of your project:

```bash
npm run docusaurus docs:version 1.0
```

The `docs` folder is copied into `versioned_docs/version-1.0`

Your docs now have 2 versions:

- `1.0` at `http://localhost:3000/docs/` for the version 1.0 docs
- `current` at `http://localhost:3000/docs/next/` for the **upcoming, unreleased docs**

### Update an existing version

It is possible to edit versioned docs in their respective folder:

- `versioned_docs/version-1.0/hello.md` updates `http://localhost:3000/docs/hello`
- `docs/hello.md` updates `http://localhost:3000/docs/next/hello`

# Installation/Development

## Installation

```console
npm install
```

## Local Development

```console
npm start
```

This command starts a local development server and opens up a browser window. Most changes are reflected live without having to restart the server.

## Build

```console
npm build
```

This command generates static content into the `build` directory and can be served using any static content hosting service/CDN. For a production-ready build it expects `PRODUCTION` as an environment variable to be set to true

## Deployment

The site is deployed to [adam-rms.com](https://adam-rms.com) through Cloudflare pages, which also generates preview deployments for all PRs