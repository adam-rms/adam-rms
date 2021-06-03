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


## Contributing 

Contributions are very welcome - please see [CONTRIBUTING.md](CONTRIBUTING.md) for a guide, or feel free to open a [Discussion](https://github.com/bstudios/adam-rms/discussions)

Database migrations, written in Phinx, are provided in `db/migrations`

### Repo Structure

Firstly, an apology. AdamRMS was, in its early life, a 2-year personal labour of love of over 600 commits pursued more as a potential commercial project than the community developed open-source project it has become today. As a result, there are no tests and documentation is limited but improving. Migrations and seeds were written in one go and as such there is no history available of the database structure before v1.47.6. Similarly, code quality and style varies as the project grew alongside its creators skill set. 

For performance reasons, AdamRMS does not use a router, and instead leaves routing to the webserver (Caddy). The project is structured around three directories within `/html`:
 
 - `admin` - The AdamRMS dashboard (the bulk of the project)
 - `common` - *(not publicly accessible)* Classes, Functions & Templates shared by both other directories
 - `public` - "Public Sites" used for instances to share their asset lists etc. with others

Within the admin/public folders there are also `common` directories which contain Classes, Functions & Templates relevant to the bulk of files within that directory & its subdirectories. 

### Ajax?....

To improve user performance & error handling this site is not based around a conventional laravel-esque form structure for user interaction. Instead, most data from the database is returned to the user through a normal dynamically loaded page, generating html from a twig template. 

When a user interacts with the page, such as pressing a button, this triggers a JQuery function (all defined in that pages' twig template) which makes an "api call" to a php script within the `admin/api/` folder and executes the change (such as an insert/delete/update). Once this completes successfully two things can happen. The first, a legacy behaviour, is that the page reloads to reflect the changes in the page itself. The second option is that the page calls a function to update what's displayed, without needing a page re-load. There are quite a few endpoints in `admin/api` that provide access to retrieving data as well, as this is how the mobile app downloads its data which it then displays. 

Since AdamRMS was setup like this in 2019, things have changed and this behaviour has been taken further to mean the leading convention is one of a static (javascript framework based) site pulling data from a RESTful API and then pushing updates, essentially removing the templating layer used by AdamRMS. This is though how the mobile app works, leveraging OnsenUI on Cordova to pull data from endpoints in the PHP and display it, then calling other endpoints for interaction. 

### Developing Locally

The following assumes you already have a local development environment setup and are familiar with deploying LAMP-style stacks locally. The deployment strategy is somewhat different, but this is covered in a separate repo.

1. Rename `example.dev.env` to `.env`, setting database credentials for your local MySQL server as you go
1. Set the document root of your local web server to `html/`
1. Run `php vendor/bin/phinx migrate` (or `vendor\bin\phinx migrate` on Windows) to set up the database
1. *If setting up for the first time* run `php vendor/bin/phinx seed:run` (or `vendor\bin\phinx seed:run` on Windows) to seed some data to allow you to login
1. Browse to `http://localhost/admin`
1. Default credentials are `username` & `password!`

Each time you pull new changes written by others you will likely need to update your local database by running the Phinx migrate command. Phinx migrations are automatically run on the production server in the dockerfile.

### Tips & Tools

- Database library used: [ThingEngineer/PHP-MySQLi-Database-Class](https://github.com/ThingEngineer/PHP-MySQLi-Database-Class)
- Debugging the public sites feature locally: set `instances_publicConfig` in the `instances` table for your instance to something like `{"enabled":true,"enableAssets":true,"enableAssetAvailability":true,"enableAssetDescriptions":true,"enableAssetNotes":true,"enableVacancies":true,"footerText":"","headerImage":null,"homepageHTML":"","customDomains":["localhost"]}`. The main thing is to make sure localhost is in customDomains. 

### Glossary of Terms

 - Instance = Business 

## OAuth

### Google

#### Endpoints Required

```
https://dash.adam-rms.com/login/index.php?google
https://dash.adam-rms.com/api/account/linkOAuth.php?google
```

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
