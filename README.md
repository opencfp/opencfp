# [![OpenCFP Banner](docs/img/banner.png)](https://github.com/opencfp/opencfp)

OpenCFP is a PHP-based conference talk submission system.

---
[![CircleCI](https://circleci.com/gh/opencfp/opencfp.svg?style=svg)](https://circleci.com/gh/opencfp/opencfp)

[![GitHub release](https://img.shields.io/github/release/opencfp/opencfp.svg)](https://github.com/opencfp/opencfp/releases/latest)

## README Contents

 * [Features](#features)
 * [Screenshots](#screenshots)
 * [Contributing](#contributing)
 * [Minimum Requirements](#requirements)
 * [Privacy Restrictions](#privacy)
 * [Installation](#installation)
   * [Grab Latest Release](#grab-latest-release)
   * [Cloning the Repository](#cloning-the-repository)
   * [Specify Environment](#specify-environment)
   * [Installing Composer Dependencies](#installing-composer-dependencies)
   * [Specify Web Server Document Root](#specify-web-server-document-root)
   * [Create a Database](#create-a-database)
   * [Configure Environment](#configure-environment)
   * [OpenCFP Central](#opencfp-central)
   * [Run Migrations](#run-migrations)
   * [Using Vagrant](#using-vagrant)
   * [Final Touches](#final-touches)
   * [Building Docker Image](#building-docker-image)
 * [Command-line Utilities](#command-line-utilities)
   * [Admin Group Management](#admin-group-management)
   * [Reviewer Group Management](#reviewer-group-management)
   * [User Management](#user-management)
   * [Clear Caches](#clear-caches)
   * [Scripts to Rule Them All](#scripts-rule-all)
 * [Compiling Frontend Assets](#compiling-frontend-assets)
 * [Testing](#testing)
 * [Troubleshooting](#troubleshooting)


## [Features](#features)

 * Speaker registration system that gathers contact information.
 * Dashboard that allows speakers to submit talk proposals and manage their profile.
 * Administrative dashboard for reviewing submitted talks and making selections.
 * Command-line utilities for administering the system.

## [Screenshots](#screenshots)

You can find screenshots of the application in our [wiki](https://github.com/opencfp/opencfp/wiki/Screenshots)


## [Contributing](#contributing)

See [`CONTRIBUTING.md`](.github/CONTRIBUTING.md).

## [Minimum Requirements](#requirements)

 * PHP 7.1+
 * Apache 2+ with `mod_rewrite` enabled and an `AllowOverride all` directive in your `<Directory>` block is the recommended web server
 * Composer requirements are listed in [composer.json](composer.json).
 * You may need to install `php7.0-intl` extension for PHP. (`php-intl` on CentOS/RHEL-based distributions)
 * Either the GD image library or the Imagick PHP extension for the [Intervention image library](http://image.intervention.io/getting_started/installation))

## [Privacy Restrictions](#privacy)

We request that anyone who installs OpenCFP to not share any of the 
personal information that is collected from users with any third
parties without explicit permission from users and to also be aware of the existence of the European
Union's [General Data Protection Regulation](https://en.wikipedia.org/wiki/General_Data_Protection_Regulation)
as it could potentially impact your ability to accept submissions
from anyone residing within the European Union.

Tools to make it easier to comply with the GDPR are in the process
of being created.

We recommend that you delete any data stored in a database associated
with an OpenCFP instance within 15 months of accepting submissions from
users.


## [Installation](#installation)

### [Grab Latest Release](#grab-latest-release)

It is recommended for you to always install the latest marked release. Go to `https://github.com/opencfp/opencfp/releases` to download it.

### [Cloning the Repository](#cloning-the-repository)

Clone this project into your working directory. We recommend always running the `master` branch as it was frequent contributions.

Example:

```
$ git clone git@github.com:opencfp/opencfp.git
Cloning into 'opencfp'...
remote: Counting objects: 4794, done.
remote: Total 4794 (delta 0), reused 0 (delta 0)
Receiving objects: 100% (4794/4794), 1.59 MiB | 10.37 MiB/s, done.
Resolving deltas: 100% (2314/2314), done.
Checking connectivity... done.
```

### [Specify Environment](#specify-environment)

OpenCFP can be configured to run in multiple environments. The application environment (`CFP_ENV`) must be specified
as an environment variable. If not specified, the default is `development`.

An example Apache configuration is provided at `/web/htaccess.dist`. Copy this file to `/web/.htaccess` or otherwise
configure your web server in the same way and change the `CFP_ENV` value to specify a different environment. The
default has been pre-set for development.

```
SetEnv CFP_ENV production
```

You will also need to set the `CFP_ENV` variable in the shell you are using when doing an install. Here are some
ways to do that with common shells assuming we're using `production`:

* bash: `export CFP_ENV=production`
* zsh:  `export CFP_ENV = production`
* fish: `set -x CFP_ENV production`

Again, just use your preferred environment in place of `production` if required.

### [Installing Composer Dependencies](#installing-composer-dependencies)

From the project directory, run the following command. You may need to download `composer.phar` first from http://getcomposer.org

```bash
$ php composer.phar run setup-env
```

If you have composer installed globally you can run:

```bash
$ composer run setup-env
```

Or you can run

```bash
$ ./script/setup
```
### [Specify Web Server Document Root](#specify-web-server-document-root)

Set up your desired webserver to point to the `/web` directory.

[Apache 2+](https://httpd.apache.org/) Example:

```
<VirtualHost *:80>
    DocumentRoot /path/to/web
    ServerName cfp.conference.com

    # Other Directives Here
</VirtualHost>
```

nginx Example:

```
server {
	server_name cfp.sitename.com;
	root /var/www/opencfp/web;
	listen 80;
	index index.php index.html index.htm;

	access_log /var/log/nginx/access.cfp.log;
	error_log /var/log/nginx/error.cfp.log;

	location / {
		try_files $uri $uri/ /index.php?$query_string;
	}

	location ~ \.php$ {
		try_files $uri =404;

		fastcgi_param CFP_ENV production;
		fastcgi_split_path_info ^(.+\.php)(/.+)$;
		fastcgi_pass unix:/var/run/php71-fpm.sock;
		fastcgi_read_timeout 150;
		fastcgi_index index.php;
		fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
		include fastcgi_params;
	}

}
```

You can use the included `opencfp-nginx.conf.dist` file and modify it as needed.

[Caddy](https://caddyserver.com) example:

```
localhost:8080
root /var/www/opencfp/web
fastcgi / 127.0.0.1:9000 php 
rewrite / {path} {path}/ /index.php/{path} 
log access.log
errors error.log
```

You can use the included `Caddyfile.dist` file and modify it as needed.

The application does not currently work properly if you use PHP's built-in
server.

### [Create a Database](#create-a-database)

Create a new database for the application to use. You will need to have the following handy to continue configuring
your installation of OpenCFP:

 * Database server hostname
 * Database name
 * Credentials to an account that can access the above database


### [Configure Environment](#configure-environment)

Depending on which environment you specified above, you will need to make a copy of the distributed configuration
schema to enter your own details into.

For example, if you specified `SetEnv CFP_ENV production`:

```bash
$ cp config/production.yml.dist config/production.yml
```

After making a local copy, edit `config/production.yml` and specify your own details. Here are some important options
to consider:

| Option                | Description                       |
|:----------------------|:----------------------------------|
| `application.enddate` | This is the date your call for proposals would end on. |
| `application.coc_link`| Set this to the link for your conference code of conduct to require speakers to agree to the code of conduct at registration |
| `secure_ssl`          | This should be enabled, if possible. Requires a valid SSL certificate. |
| `database.*`          | This is the database information you collected above. |
| `mail.*`              | This is SMTP configuration for sending mail. The application sends notifications on various system events. |
| `opencfpcentral.*`    | Settings related to using OpenCFP Central for single-sign-on |
| `talk.categories.*`   | dbkey: Display Name mapping for your talk categories |
| `talk.types.*`        | dbkey: Display Name mapping for your talk types |
| `talk.levels.*`       | dbkey: Display Name mapping for your talk levels |


For example, if you wanted to setup Mailgun as your email provider, your mail configuration would look something like this:

```
mail:
    host: smtp.mailgun.org
    port: 587
    username: do-not-reply@cfp.myfancyconference.com
    password: "a1b2c3d4"
    encryption: tls
    auth_mode: ~
```
### [Running behind a trusted proxy](#run-trusted-proxy)

If you are running OpenCFP behing a proxy server which adds X-Forwarded-For headers (this could be a cloud based load balancer or a service such as Cloudflare) you will need to set the environment variable TRUST_PROXIES to true this will ensure that OpenCFP trusts the headers set by these proxies for the original IP address and ssl mode. Setting this will trust these headers regardless of where the original request originates, so it's advisable to either lock down your instance so that only the trusted proxy can access it or modify the list of trusted proxies in the index.php file to only include the ip addresses of your proxies.


### [OpenCFP Central](#opencfp-central)

[OpenCFP Central](https://www.opencfpcentral.com) is a web site created by Chris Hartjes to help both speakers and conference organizers. Conference
organizers can create an account and get an OAuth2 client ID and client secret, which can be entered into the
configuration files. With the ID and secret, change the values for `opencfpcentral.sso` to be `on`, and set
`opencfpcentral.clientId` and `opencfpcentral.clientSecret` to their respective values. 

Any users who create an account at OpenCFP Central can use it to log into any OpenCFP instance that has enabled
SSO. If you don't already have an account on that instance, one will be created for you.


### [Run Migrations](#run-migrations)

This project uses [Phinx](http://phinx.org) to handle migrations. Configuration for Phinx is loaded from [`phinx.php`](phinx.php). 
The `CFP_ENV` environment variable is used to select an environment to migrate and defaults to `development`. Be sure 
to correctly configure the app using the `config/:environment.yml` files.

To run migrations, make sure you are in the root directory for the project and run the following:

```
$ CFP_ENV=production vendor/bin/phinx migrate
```

Note: For updating previously installed instances only run migrations as needed.

### [Using Vagrant](#using-vagrant)

After running `$ composer run setup-env` (or `$ ./script/setup`) from the project root run `php vendor/bin/homestead make`. 
This will create a `Homestead.yaml` based on settings from `Homestead.yaml.example`. Do not version control `Homestead.yaml`

Run `vagrant up`
Add `192.168.10.10 opencfp.test` to your operating system's hosts file (/etc/hosts)
Point your browser to `http://opencfp.test`

Edit your `config/development.yml` to use Homestead's database & mail settings:

```
database:
  host: 127.0.0.1
  database: cfp
  dsn: mysql:dbname=cfp;host=127.0.0.1
  user: homestead
  password: secret

log:
  level: debug

mail:
  host: localhost
  port: 1025
  username: ~
  password: ~
  encryption: ~
  auth_mode: ~
```

Mailhog (local mail catching) can be viewed at http://opencfp.test:8025

For more usage information please see the [Laravel Homestead Docs](http://laravel.com/docs/homestead)

### [Final Touches](#final-touches)

 * The web server must be able to write to the directories:
    * `/web/uploads`
    * `/cache/:environment` (e.g. `/cache/production`)
    * `/log`
 * You may need to alter the `memory_limit` of the web server to allow image processing of head-shots. This is largely
   dictated by the size of the images people upload. Typically 512M works.
 * Customize templates and `/web/assets/css/app.css` to your heart's content.

### [Building Docker Image](#building-docker-image)

#### What is Docker

Quoting [OpenSource](https://opensource.com/resources/what-docker):

"[Docker](https://www.docker.com) is a tool designed to make it easier to create, deploy, and run applications by using containers. 
Containers allow a developer to package up an application with all of the parts it needs, such as libraries and 
other dependencies, and ship it all out as one package. By doing so, thanks to the container, 
the developer can rest assured that the application will run on any other Linux machine regardless of any customized 
settings that machine might have that could differ from the machine used for writing and testing the code."

#### Requirements

1. You will need to download and install [Docker](https://www.docker.com/get-docker) locally.
2. You will need to download and install [docker-compose](https://docs.docker.com/compose/install/) too.

#### Build & Run the image

Please remember to edit the file `config/docker.yml.dist` to match your environment, then you can build your own 
docker image by executing:

```
$ ./.docker/build latest
```

And the result will be an image called `opencfp/opencfp:latest`.

Or if you like you can run [docker-compose](https://docs.docker.com/compose/install/) command which will build the 
image and run the containers automatically for you:

```
$  docker-compose -f docker-compose.yml.dist up --build -d
```

So now if you head over to `http://localhost` you will be greeted with a running version of OpenCFP.

After building and running the Docker image you'll need to [Run Migrations](#run-migrations) and [Add an Admin User](#user-management) before logging-in.

#### Run PHP commands within the Container

To run any command in the app container you can use the docker-compose 
[exec](https://docs.docker.com/compose/reference/exec/) command, for example to run the `setup` script you run:

```bash
$ docker-compose -f docker-compose.yml.dist exec app composer run setup-env
```
OR

```bash
$ docker-compose -f docker-compose.yml.dist exec app ./script/setup
```

#### Running the image directly

You can run the image (after you build it) and link it to an already running database container using the docker 
[run](https://docs.docker.com/engine/reference/commandline/run/) command like:

```
docker run -e CFP_ENV=production -e CFP_DB_HOST=database -e CFP_DB_PASS=root --name cfp --link database:database -p 80:80 -d opencfp/opencfp:latest
```

Where `database` is the name of the running database container. 


#### Access MySQL container

To access MySQL you can use the following information:

- **Host**: 127.0.0.1
- **User**: root
- **Password**: root (or the one you specified in docker-compose) 
 

For using docker in your development environment check [DOCKER.md](DOCKER.md) file. 

_PS_: You can always modify the file `docker-compose.yml.dist` and have your own setup.


## [Command-line Utilities](#command-line-utilities)

OpenCFP comes bundled with a few command-line utilities to administer the system. A full list of commands (along with help for each)
can be found by running the following in the project root:

```
$ bin/console
```

### [Admin Group Management](#admin-group-management)

Administrators are authorized to review speaker information in addition to specifying talk favorites and making selections.

Adding `speaker@opencfp.org` to the admin group:

```
$ bin/console user:promote --env=production speaker@opencfp.org admin
```

Removing `speaker@opencfp.org` from the admin group:

```
$ bin/console user:demote --env=production speaker@opencfp.org admin
```

### [Reviewer Group Management](#reviewer-group-management)
Reviewers are authorized to see talks and give ratings to them.

Adding `speaker@opencfp.org` to the reviewer group:

```
$ bin/console user:promote --env=production speaker@opencfp.org reviewer
```

Removing `speaker@opencfp.org` from the reviewer group:

```
$ bin/console user:demote --env=production speaker@opencfp.org reviewer
```


### [User Management](#user-management)

Users are needed for you system, and sometimes you want to add users via command line.

Adding a speaker:

```
$ bin/console user:create --first_name="Speaker" --last_name="Name" --email="speaker@opencfp.org" --password="somePassw0rd!"
```

Add an admin:

```
$ bin/console user:create --first_name="Admin" --last_name="Name" --email="admin@opencfp.org" --password="somePassw0rd!" --admin
```

Add a reviewer:

```
$ bin/console user:create --first_name="Admin" --last_name="Name" --email="admin@opencfp.org" --password="somePassw0rd!" --reviewer
```

### [Clear Caches](#clear-caches)

OpenCFP uses Twig as a templating engine and HTML Purifier for input filtering. Both of these packages maintain a cache.
If you need to clear all application caches:

```
$ bin/console cache:clear
```

### [Scripts to Rule Them All](#scripts-rule-all)

OpenCFP follows the [Scripts to Rule Them All](https://github.com/github/scripts-to-rule-them-all) pattern. This allows
for an easy to follow convention for common tasks when developing applications.

#### Initial Setup
This command will install all dependencies, run database migrations, and alert you of any missing configs.

```
$ composer run setup-env
```

#### Update Application
This command will update all dependencies and run new migrations

```
$ composer run update-env
```

#### Run Tests
This command will run the PHPUnit test suite using distributed phpunit config, `phpunit.xml.dist`, if
no phpunit.xml is found in the root.

```
$ composer run test
```

## [Compiling Frontend Assets](#compiling-frontend-assets)

OpenCFP ships with a pre-compiled CSS file. However, we now include the Sass / PostCSS used to compile front-end assets. You are free to modify these source files to change brand colors or modify your instance however you see fit. Remember, you can do **nothing** and take advantage of the pre-compiled CSS we ship. You only need these instructions if you want to customize or contribute to the look and feel of OpenCFP. Let's take a look at this new workflow for OpenCFP.

Install Node dependencies using `yarn`.

```bash
yarn install
```

Now dependencies are installed and we're ready to compile assets. Check out the `scripts` section of `package.json`. A normal development workflow is to run either `yarn run watch` or `yarn run watch-poll` (for OS that don't have `fs-events`) and begin work. When you make changes to Sass files, Webpack will recompile assets, but it doesn't compress the output. To do that, run `yarn run prod` (an alias for `yarn run production`). This will run the same compilation, but will compress the output.

The main `app.scss` file is at [`resources/assets/sass/app.scss`](resources/assets/sass/app.scss). We use [Laravel Mix](https://github.com/JeffreyWay/laravel-mix) to compile our Sass. Mix is configurable to run without Laravel, so we take advantage of that because it really makes dealing with Webpack a lot simpler. Our Mix configuration is at [`webpack.mix.js`](webpack.mix.js). In it, we run our `app.scss` through a Sass compilation step, we copy FontAwesome icons and finally run the compiled CSS through [Tailwind CSS](https://tailwindcss.com), a PostCSS plugin.

TailwindCSS is a new utility-first CSS framework that uses CSS class composition to piece together interfaces. Check out [their documentation](https://tailwindcss.com/docs/what-is-tailwind/) for more information on how to use the framework. We use it extensively across OpenCFP and it saves a lot of time and keeps us from having to maintain *too much* CSS. If you take a look through our `app.scss`, you'll see a lot of calls to [`@apply`](https://tailwindcss.com/docs/functions-and-directives#apply). This is NOT a Sass construct. It's a TailwindCSS function used to mixin existing classes into our custom CSS.

## [Testing](#testing)

There is a test suite that uses PHPUnit in the /tests directory. To set up
your environment for testing:

1. Create a testing database, and update the name and credentials in
   /config/testing.yml
2. The recommended way to run the tests is:

```
$ composer run test
```

The default phpunit.xml.dist file is in the root directory for the project.

## [Troubleshooting](#troubleshooting)

**I'm getting weird permissions-related errors to do with HTML Purifier.**

You may need to edit directory permissions for some vendor packages such as HTML Purifier. Check the `/cache` directory's
permissions first.

