# AdamRMS

AdamRMS is an advanced Rental Management System for Theatre, AV & Broadcast, written in PHP with the Twig Templating engine, and deployed using a pre-built docker container.

It is available as a hosted solution (not currently open to new customers) or to be self-hosted as a docker container. 

![Banner](.github/banner.jpg)

## Repositories

 - [*This repo* Project Source Code __adam-rms__](https://github.com/bstudios/adam-rms)
 - [Mobile App __adam-rms-app__](https://github.com/bstudios/adam-rms-app)
 - [Deployment Script Templates __adam-rms-deployment__](https://github.com/bstudios/adam-rms-deployment)
 - [Image Compressor Worker __adam-rms-imageCompressor__](https://github.com/bstudios/adam-rms-imageCompressor)
 - [File Deleter Worker __adam-rms-s3Deletor__](https://github.com/bstudios/adam-rms-s3Deletor)
 - [Public Marketing Site __adam-rms-website__](https://github.com/bstudios/adam-rms-website)

## Docker Images

 - Docker Hub: [bithellstudios/adam-rms](https://hub.docker.com/r/bithellstudios/adam-rms)
 - GitHub Packages: [bstudios/adam-rms](https://github.com/orgs/bstudios/packages?repo_name=adam-rms)

## Developing Locally

The following assumes you already have a local development environment setup and are familiar with deploying LAMP-style stacks locally. The deployment strategy is somewhat different, but this is covered in a seperate repo. 

1. An example database is provided as `database.sql` - this can be run in MySQL 8 to provide a local development database.
1. Rename `example.dev.env` to `.env`
1. Set the document root of your local web server to `html/`
1. Browse to `http://localhost/admin`
1. Default credentials are `username` & `password!`

## Contributing 

Contributions are very welcome - please see [CONTRIBUTING.md](CONTRIBUTING.md) for a guide, or feel free to open a [Discussion](https://github.com/bstudios/adam-rms/discussions)

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
