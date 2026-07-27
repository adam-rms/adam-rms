---
sidebar_position: 1
title: Introduction
---

# Local Development

:::caution

This documentation assumes a strong grounding in both client side Javascript and Server Side PHP

:::


## Repo Structure

Firstly, an apology. AdamRMS was, in its early life, a 2-year personal labour of love of over 600 commits pursued more as a potential commercial project than the community developed open-source project it has become today. As a result, there are no tests and documentation is limited but improving. Migrations and seeds were written in one go and as such there is no history available of the database structure before v1.47.6. Similarly, code quality and style varies as the project grew alongside its creators skill set. 

For performance reasons, AdamRMS does not use a router, and instead leaves routing to the webserver (Caddy). The project is structured around two directories within `/html`:
 
 - `admin` - The AdamRMS dashboard
 - `common` - *(not publicly accessible)* A handful of Classes, Functions & Templates

Database migrations, written in Phinx, are provided in `db/migrations`

### Ajax?....

To improve user performance & error handling this site is not based around a conventional laravel-esque form structure for user interaction. Instead, most data from the database is returned to the user through a normal dynamically loaded page, generating html from a twig template. 

When a user interacts with the page, such as pressing a button, this triggers a JQuery function (all defined in that pages' twig template) which makes an "api call" to a php script within the `admin/api/` folder and executes the change (such as an insert/delete/update). Once this completes successfully two things can happen. The first, a legacy behaviour, is that the page reloads to reflect the changes in the page itself. The second option is that the page calls a function to update what's displayed, without needing a page re-load. There are quite a few endpoints in `admin/api` that provide access to retrieving data as well, as this is how the mobile app downloads its data which it then displays. 

Since AdamRMS was setup like this in 2018, things have changed and this behaviour has been taken further to mean the leading convention is one of a static (javascript framework based) site pulling data from a RESTful API and then pushing updates, essentially removing the templating layer used by AdamRMS. This is though how the mobile app works, leveraging OnsenUI on Cordova to pull data from endpoints in the PHP and display it, then calling other endpoints for interaction. 

## Developing Locally

### Recommended: Docker Devcontainer

[![Open in GitHub Codespaces](https://github.com/codespaces/badge.svg)](https://github.com/codespaces/new?ref=main&repo=217888995)

This repo has a configured devcontainer for use with GitHub Codespaces or VSCode. If you have a GitHub Codespaces subscription (paid), you can use this to get started with the project in the web, or if you have access to VSCode on your computer (free) you can get started by cloning the repo and opening it in VSCode, then [opening the project in a devcontainer](https://code.visualstudio.com/docs/devcontainers/tutorial).

When creating the container, a database will be created but it will be empty. 

To setup some test data, press `Ctrl+Shift+P` then type `tasks: run task` - then select the `Seed Database` task which will be listed. If you already have a database container **it will be truncated first - meaning all data will be lost**. The database will be available on port 3306, and the webserver on port 8080. VSCode will automatically open the webserver in a browser window.

You can then press the debug menu on the left and select `Launch AdamRMS with debugger` and the login screen will show. Default credentials are `username` & `password!`

### Harder: Installing Locally

The following assumes you already have a local development environment setup and are familiar with deploying LAMP-style stacks locally. The deployment strategy is somewhat different, but this is covered in a [separate repo](https://github.com/adam-rms/adam-rms-deployment).

1. Rename `example.dev.env` to `.env`, setting database credentials for your local MySQL server as you go
1. Set the document root of your local web server to `html/`
1. Run `php vendor/bin/phinx migrate` (or `vendor\bin\phinx migrate` on Windows) to set up the database
1. *If setting up for the first time* run `php vendor/bin/phinx seed:run` (or `vendor\bin\phinx seed:run` on Windows) to seed some data to allow you to login. **NB: This will delete all data in the database by running the seed command**
1. Browse to `http://localhost/admin`
1. Default credentials are `username` & `password!`

Each time you pull new changes written by others you will likely need to update your local database by running the Phinx migrate command. Phinx migrations are automatically run on the production server in the dockerfile.

## Tips & Tools

- Database library used: [ThingEngineer/PHP-MySQLi-Database-Class](https://github.com/ThingEngineer/PHP-MySQLi-Database-Class)
