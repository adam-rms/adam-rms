---
sidebar_position: 2
title: Getting Started
---

## Installation/Development

### Installation

```console
npm install
```

### Local Development

```console
npm start
```

This command starts a local development server and opens up a browser window. Most changes are reflected live without having to restart the server.

### Build

```console
npm run build
```

This command generates static content into the `build` directory and can be served using any static content hosting service/CDN. 

For a production-ready build it expects the `CONTEXT` environment variable to be set to `production` (this is set automatically by Netlify and Cloudflare Pages).