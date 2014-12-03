# [![OpenCFP Banner](docs/img/banner.png)](https://github.com/chartjes/opencfp)

OpenCFP is a PHP-based conference talk submission system.

---
[![Build Status](https://travis-ci.org/chartjes/opencfp.svg?branch=master)](https://travis-ci.org/chartjes/opencfp)
[![Code Climate](https://codeclimate.com/github/chartjes/opencfp/badges/gpa.svg)](https://codeclimate.com/github/chartjes/opencfp)
[![Test Coverage](https://codeclimate.com/github/chartjes/opencfp/badges/coverage.svg)](https://codeclimate.com/github/chartjes/opencfp)

## README Contents

 * [Features](#features)
 * [Contributing](#contributing)
 * [Requirements](#requirements)
 * [Installation](#installation)
   * [Cloning the Repository](#cloning-the-repository)
   * [Installing Composer Dependencies](#installing-composer-dependencies)
   * [Create a Database](#create-a-database)
   * [Specify Environment](#specify-environment)
   * [Configure Environment](#configure-environment)
   * [Run Migrations](#run-migrations)
   * [Final Touches](#final-touches)
 * [Command-line Utilities](#command-line-utilities)
   * [Admin Group Management](#admin-group-management)
   * [Clear Caches](#clear-caches)
 * [Testing](#testing)
 * [Troubleshooting](#troubleshooting)

<a name="features" />
## Features

 * Speaker registration system that gathers contact information.
 * Dashboard that allows speakers to submit talk proposals and manage their profile.
 * Administrative dashboard for reviewing submitted talks and making selections.
 * Command-line utilities for administering the system.
 * JSON-API for selected use-cases. (Coming Soon!)

<a name="contributing" />
## Contributing

We welcome and love contriubtions! To facilitate receiving updates to OpenCFP, we encourage you to create a new
personal branch after you fork this repository. This branch should be used for content and changes that are specific
to your event. However, anything you are willing to push back should be updated in your master branch. This will help
keep the master branch generic for future event organizers that choose to use the system. You would then be able to
merge master to your private branch and get updates when desired!

<a name="requirements" />
## Requirements

 * PHP 5.4+
 * Apache 2+ with `mod_rewrite` enabled and an `AllowOverride all` directive in your `<Directory>` block.
 * Composer requirements are listed in [composer.json](composer.json).
 * You may need to install `php5-intl` extension for PHP. (`php-intl` on CentOS/RHEL-based distributions)

<a name="installation" />
## Installation

<a name="cloning-the-repository" />
### Cloning the Repository

Clone this project into your working directory.

Example:

```
$ git clone git@github.com:chartjes/opencfp.git
Cloning into 'opencfp'...
remote: Counting objects: 4794, done.
remote: Total 4794 (delta 0), reused 0 (delta 0)
Receiving objects: 100% (4794/4794), 1.59 MiB | 10.37 MiB/s, done.
Resolving deltas: 100% (2314/2314), done.
Checking connectivity... done.
```

<a name="installing-composer-dependencies" />
### Installing Composer Dependencies

From the project directory, run the following command. You may need to download `composer.phar` first from http://getcomposer.org

```bash
$ php composer.phar install
```

<a name="specify-web-server-document-root" />
### Specify Web Server Document Root

Set up your desired webserver to point to the `/web` directory.

Apache 2+ Example:

```
<VirtualHost *:80>
    DocumentRoot /path/to/web
    ServerName cfp.conference.com

    # Other Directives Here
</VirtualHost>
```

<a name="create-a-database" />
### Create a Database

Create a new database for the application to use. You will need to have the following handy to continue configuring
your installation of OpenCFP:

 * Database server hostname
 * Database name
 * Credentials to an account that can access the above database

<a name="specify-environment" />
### Specify Environment

OpenCFP can be configured to run in multiple environments. The application environment (`CFP_ENV`) must be specified
as an environment variable. If not specified, the default is `development`.

Add the following to the Apache `.htaccess` file at `/web/.htaccess` to specify a new environment variable.

```
SetEnv CFP_ENV production
```

<a name="configure-environment" />
### Configure Environment

Depending on which environment you specified above, you will need to make a copy of the distributed configuration
schema to enter your own details into.

For example, if you specified `SetEnv CFP_ENV production`:

```bash
$ cp config/production.dist.yml config/production.yml
```

After making a local copy, edit `config/production.yml` and specify your own details. Here are some important options
to consider:

| Option                | Description                       |
|:----------------------|:----------------------------------|
| `application.enddate` | This is the date your call for proposals would end on. |
| `secure_ssl`          | This should be enabled, if possible. Requires a valid SSL certificate. |
| `database.*`          | This is the database information you collected above. |
| `mail.*`              | This is SMTP configuration for sending mail. The application sends notifications on various system events. |

<a name="run-migrations" />
### Run Migrations

This project uses [Phinx](http://phinx.org) to handle migrations. Be sure to copy the `phinx.yml.dist` file that is in the
root directory for the project to `phinx.yml` and edit it to match your own database settings.

To run migrations, make sure you are in the root directory for the project and run the following:

```
$ vendor/bin/phinx migrate --environment=production
```

<a name="final-touches" />
### Final Touches

 * The web server must be able to write to the `/web/uploads` directory in order to
 * You may need to alter the `memory_limit` of the web server to allow image processing of head-shots. This is largely
   dictated by the size of the images people upload. Typically 512M works.
 * Customize templates and `/web/assets/css/site.css` to your heart's content.

<a name="command-line-utilities" />
## Command-line Utilities

OpenCFP comes bundled with a few command-line utilities to administer the system. A full list of commands (along with help for each)
can be found by running the following in the project root:

```
$ bin/opencfp
```

<a name="admin-group-management" />
### Admin Group Management

Administrators are authorized to review speaker information in addition to specifying talk favorites and making selections.

Adding `speaker@opencfp.org` to the admin group:

```
$ bin/opencfp admin:promote --env=production speaker@opencfp.org
```

Removing `speaker@opencfp.org` from the admin group:

```
$ bin/opencfp admin:demote --env=production speaker@opencfp.org
```

<a name="clear-caches" />
### Clear Caches

OpenCFP uses Twig as a templating engine and HTML Purifier for input filtering. Both of these packages maintain a cache,
if enabled. If you need to clear all application caches:

```
$ bin/opencfp cache:clear
```

<a name="testing" />
## Testing

There is a test suite that uses PHPUnit in the /tests directory. The recommended way to run the tests is:

```
$ ./vendor/bin/phpunit -c tests/phpunit.xml
```

<a name="troubleshooting" />
## Troubleshooting

**I'm getting weird permissions-related errors to do with HTML Purifier.**

You may need to edit directory permissions for some vendor packages such as HTML Purifier. Check the `/cache` directory's
permissions first (if you have `cache.enabled` set to `true`).
