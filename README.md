<p align="center">
  <a href="https://adam-rms.com/" target="blank"><img src="assets/marketing/github-readme-logo.png" width="420" alt="Logo" /></a>
</p>

AdamRMS is an advanced Rental Management System for Theatre, AV & Broadcast, written in Typescript. It is available as a hosted solution or to be self-hosted.

![GitHub release (latest by date)](https://img.shields.io/github/v/release/bstudios/adam-rms)
![GitHub repo size](https://img.shields.io/github/repo-size/bstudios/adam-rms)
![GitHub issues](https://img.shields.io/github/issues/bstudios/adam-rms)
![GitHub closed issues](https://img.shields.io/github/issues-closed/bstudios/adam-rms)
![GitHub pull requests](https://img.shields.io/github/issues-pr/bstudios/adam-rms)
![GitHub closed pull requests](https://img.shields.io/github/issues-pr-closed/bstudios/adam-rms)
![GitHub](https://img.shields.io/github/license/bstudios/adam-rms)
![GitHub stars](https://img.shields.io/github/stars/bstudios/adam-rms)
![GitHub contributors](https://img.shields.io/github/contributors/bstudios/adam-rms)
![GitHub](https://img.shields.io/github/release/bstudios/adam-rms/all)

![Banner](assets/marketing/banner.jpg)

## The Repo

> **This branch is the development branch for v2, which is not currently released.** For the current version of AdamRMS see the [v1 branch](https://github.com/bstudios/adam-rms/tree/v1).

This repo is a [monorepo](https://www.atlassian.com/git/tutorials/monorepos), containing three distinct applications:

Directory|Description||
:-----|:-----|:----
`/api`|NestJS API|[Docs](/api)
`/app`|React App|[Docs](/app)
`/docs`|Public Facing Marketing & Documentation Website|[Docs](/docs)
`/assets`|*Logos/Marketing Assets*|

[VSCode](https://github.com/microsoft/vscode) is recommended for development, and debug profiles are provided for all three applications. Be sure to run `npm install` in each directory first before attempting to use run them. 

## The Stack

- [TypeScript](https://github.com/microsoft/TypeScript): Programming Language

### API

- [NestJS](https://github.com/nestjs/nest): API Framework
- [TypeORM](https://github.com/typeorm/typeorm): Database ORM
- [MySQL](https://github.com/mysqljs/mysql): Database
- [Passport](https://github.com/jaredhanson/passport): Authentication
- [Express](https://github.com/expressjs/express): Webserver
- [Swagger](https://github.com/swagger-api/swagger-ui): API Docs

### App

- [Ionic Framework](https://github.com/ionic-team/ionic-framework): Frontend Framework
- [Capacitor](https://github.com/ionic-team/capacitor): Native Code Execution Layer

### Public Website

- [Docusaurus](https://github.com/facebook/docusaurus): Public Site & User Guide

## Developing, Releases & Contributing 

Contributions are very welcome - please see [CONTRIBUTING.md](CONTRIBUTING.md) for a guide, or feel free to open a [Discussion](https://github.com/bstudios/adam-rms/discussions)

Contributions to versions 2.x are governed by an Individual Contributor License Agreement, and cannot be merged until this agreement has been signed.

## Repositories

> AdamRMS has only recently become a monorepo, before this it was a large set of different repositories.

Repo|Repo Name||Link
:-----|:-----|:-----|:-----
Project Source Code & Public Site|adam-rms| :white_check_mark: |https://github.com/bstudios/adam-rms
v1 Deployment Script Templates|adam-rms-deployment|:white_check_mark:|https://github.com/bstudios/adam-rms-deployment
v1 Mobile App|adam-rms-app|__DEPRECATED__|https://github.com/bstudios/adam-rms-app
v1 Image Compressor Worker|adam-rms-imageCompressor|__DEPRECATED__|https://github.com/bstudios/adam-rms-imageCompressor
v1 File Deleter Worker|adam-rms-s3Deletor|__DEPRECATED__|https://github.com/bstudios/adam-rms-s3Deletor
Old Status Site|adam-rms-status|__DEPRECATED__|https://github.com/bstudios/adam-rms-status

## Licence

AdamRMS is a web-based Rental Management System

Copyright (C) 2019-2021 Bithell Studios Ltd.

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published
by the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.

![This website is hosted Green - checked by thegreenwebfoundation.org](https://api.thegreenwebfoundation.org/greencheckimage/adam-rms.com?nocache=true)
