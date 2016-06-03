# [![OpenCFP Banner](docs/img/banner.png)](https://github.com/opencfp/opencfp)

OpenCFP is a PHP-based conference talk submission system.

---
[![Build Status](https://travis-ci.org/opencfp/opencfp.svg?branch=master)](https://travis-ci.org/opencfp/opencfp)
[![Code Climate](https://codeclimate.com/github/opencfp/opencfp/badges/gpa.svg)](https://codeclimate.com/github/opencfp/opencfp)
[![Test Coverage](https://codeclimate.com/github/opencfp/opencfp/badges/coverage.svg)](https://codeclimate.com/github/opencfp/opencfp)

## README Contents

 * [Features](#features)
 * [Screenshots](#screenshots)
 * [Contributing](#contributing)
 * [Requirements](#requirements)
 * [Installation](#installation)
   * [Cloning the Repository](#cloning-the-repository)
   * [Specify Environment](#specify-environment)
   * [Installing Composer Dependencies](#installing-composer-dependencies)
   * [PHP Built-in Web Server](#php-built-in-web-server)
   * [Create a Database](#create-a-database)
   * [Configure Environment](#configure-environment)
   * [Run Migrations](#run-migrations)
   * [Final Touches](#final-touches)
 * [JSON API](#json-api)
   * [Configuration](#json-api-configuration)
   * [Authorization](#json-api-authorization)
   * [Endpoints](#json-api-endpoints)
   * [Using the API](#json-api-usage)
 * [Command-line Utilities](#command-line-utilities)
   * [Admin Group Management](#admin-group-management)
   * [Clear Caches](#clear-caches)
   * [Scripts to Rule Them All](#scripts-rule-all)
 * [Testing](#testing)
 * [Troubleshooting](#troubleshooting)

<a name="features" />
## Features

 * Speaker registration system that gathers contact information.
 * Dashboard that allows speakers to submit talk proposals and manage their profile.
 * Administrative dashboard for reviewing submitted talks and making selections.
 * Command-line utilities for administering the system.
 * JSON-API for selected use-cases. (Coming Soon!)

<a name="screenshots" />
## Screenshots
![Front page](http://i.imgur.com/GDhX1lD.png)
![Login screen](http://i.imgur.com/VfNNch9.png)
![Speaker page](http://i.imgur.com/uw1qmbS.png)
![Talk page](http://i.imgur.com/pSreRoM.png)
![Admin area](http://i.imgur.com/1Vmnwbv.png)
![Admin talk review](http://i.imgur.com/3IRXDMg.png)
![Admin speaker details](http://i.imgur.com/3oSXzGQ.png)
![Admin talks dashboard](http://i.imgur.com/6Uu0OZu.png)

<a name="contributing" />
## Contributing

We welcome and love contributions! To facilitate receiving updates to OpenCFP, we encourage you to create a new
personal branch after you fork this repository. This branch should be used for content and changes that are specific
to your event. However, anything you are willing to push back should be updated in your master branch. This will help
keep the master branch generic for future event organizers that choose to use the system. You would then be able to
merge master to your private branch and get updates when desired!

Here are some issues that we would love to see contributions for:

(Last updated November 19, 2015)

* [Remind users with no talks submitted to send something](https://github.com/opencfp/opencfp/issues/159)
* [Transition from Sentry to Silex Security](https://github.com/opencfp/opencfp/issues/163)
* [Allow users to specify length of workshops](https://github.com/opencfp/opencfp/issues/254)

<a name="requirements" />
## Requirements

 * PHP 5.5+
 * Apache 2+ with `mod_rewrite` enabled and an `AllowOverride all` directive in your `<Directory>` block is the recommended web server
 * Composer requirements are listed in [composer.json](composer.json).
 * You may need to install `php5-intl` extension for PHP. (`php-intl` on CentOS/RHEL-based distributions)

<a name="installation" />
## Installation

<a name="cloning-the-repository" />
### Cloning the Repository

Clone this project into your working directory.

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

<a name="specify-environment" />
### Specify Environment

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

bash: `export CFP_ENV = production`
zsh:  `export CFP_ENV = production`
fish: `set -x CFP_ENV production`

Again, just use your preferred environment in place of `production` if required. 

<a name="installing-composer-dependencies" />
### Installing Composer Dependencies

From the project directory, run the following command. You may need to download `composer.phar` first from http://getcomposer.org

```bash
$ script/setup
```

<a name="php-built-in-web-server" />
### PHP Built-in Web Server

To run OpenCFP using [PHP's built-in web server](http://php.net/manual/en/features.commandline.webserver.php) the
following command can be run:

```
$ script/server
```

The server uses port `8000`. This is a quick way to get started doing development on OpenCFP itself.

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

nginx Example:

```
server{
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
		fastcgi_pass unix:/var/run/php5-fpm.sock;
		fastcgi_read_timeout 150;
		fastcgi_index index.php;
		fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
		include fastcgi_params;
	}

}
```

The application does not currently work properly if you use PHP's built-in
server.

<a name="create-a-database" />
### Create a Database

Create a new database for the application to use. You will need to have the following handy to continue configuring
your installation of OpenCFP:

 * Database server hostname
 * Database name
 * Credentials to an account that can access the above database


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

<a name="run-migrations" />
### Run Migrations

This project uses [Phinx](http://phinx.org) to handle migrations. Be sure to copy the `phinx.yml.dist` file that is in the
root directory for the project to `phinx.yml` and edit it to match your own database settings.

To run migrations, make sure you are in the root directory for the project and run the following:

```
$ vendor/bin/phinx migrate --environment=production
```

Note: For updating previously installed instances only run migrations as needed.

<a name="final-touches" />
### Final Touches

 * The web server must be able to write to the `/web/uploads` directory in order to
 * You may need to alter the `memory_limit` of the web server to allow image processing of head-shots. This is largely
   dictated by the size of the images people upload. Typically 512M works.
 * Customize templates and `/web/assets/css/site.css` to your heart's content.

<a name="json-api" />
## JSON API

OpenCFP has a JSON API (not to be confused with the [json-api specification](http://jsonapi.org/)) that can be used by
third-party applications to take advantage of a set of features on behalf of a user. The API is enabled by default, but
can be disabled if not needed for your instance of OpenCFP.

<a name="json-api-configuration" />
### API Configuration

Configuration for the API is stored under the `api` namespace of your configuration YAML file. Currently, there is only
one available configuration setting: whether or not the api is `enabled`.

<a name="json-api-authorization" />
### Authorization

In order to use any of the available APIs in order to do work on a OpenCFP user's behalf, an OAuth2 token must be
provided and must have appropriate OAuth2 scope(s) associated with it. Interacting with the authorization endpoints
is very much the same as any other OAuth2 implementation; You'll register your custom web application as a Client Application
with OpenCFP and from there, you can start to send folks through the [Authorization Code Grant Flow](https://tools.ietf.org/html/rfc6749#section-4.1)
in order to eventually obtain a bearer token to act on their behalf.

There are some caveats to the above description that may differ from what you're used to in interacting with the typical
OAuth2 implementation:

- Some users **you** send through the OAuth2 process will **not** have an account on the target instance of OpenCFP. We take care of that ([described below](#api-usage-scenario)).
- You will not have to create an account on the target OpenCFP process to register your custom web application as an OAuth2 Client Application. We implement a subset of the [OAuth 2.0 Dynamic Client Registration Protocol](https://tools.ietf.org/html/draft-ietf-oauth-dyn-reg-23) draft to allow applications to dynamically register themselves as Client Applications.

With all of that out of the way, here are some nuts and bolts about our implementation of OAuth2:

- We only support two grant types: Authorization Code & Refresh Token. This allows you to do work on behalf of any OpenCFP user (if authorized) and renew that authorization (bearer token) when it expires.
- Bearer tokens have a time-to-live (TTL) of `3600` seconds (1 hour). Expired tokens will be rejected and you have the option of refreshing or requesting a new token. This may be configurable in the future.
- Refresh tokens have a TTL of `604800` seconds (1 week). If you do not want to put users through the authorization code grant flow weekly, have automation rotate you access tokens.
- It is **highly recommended** to only enable this API if you have a valid SSL certificate. OAuth2's security mechanisms are 100% reliant on TLS.
- Authorization endpoints are described below.

<a name="json-api-endpoints" />
### Endpoints

This serves as a high-level overview of the OpenCFP API.

**Authorization**

Authorization endpoints are used as part of the process for obtaining and renewing an Access Token representing a user's
authorization for you (as a client developer) to act on their behalf. A step-by-step [usage scenario](#api-usage-scenario) is
described for convenience below.

| Method | Route | Description |
| --- | --- | --- |
| `GET` | `/oauth/authorize` | Starts the authorization flow. |
| `POST` | `/oauth/access_token` | Used to trade an Authorization Code for an Access Token. |
| `POST` | `/oauth/clients` | Client registration endpoint for web application to register as a Client Application. |

**Speaker Profile API**

The Speaker Profile API allows you to look up information about the currently authenticated user. You might use this to
populate attributes in your own custom application based on a user's profile in a target instance of OpenCFP.

| Method | Route | Description |
| --- | --- | --- |
| `GET` | `/api/me` | Returns JSON body representing information about the authenticated user. |

**Talks API**

The Talks API allows you to manage the collection of submitted talks for the currently authenticated user.

| Method | Route | Description |
| --- | --- | --- |
| `POST` | `/api/talks` | Given JSON payload representing a talk, creates talk for authenticated user and issues a 201 Created upon success, appropriate error otherwise. |
| `GET` | `/api/talks` | Returns JSON collection of all talks for authenticated user. |
| `GET` | `/api/talks/{id}` | Returns a particular talk for authenticated user. Returns appropriate responses for unauthorized or non-existent talks. |
| `PUT` | `/api/talks/{id}` | **Not Implemented** Updates a particular talk. Partial updates are supported through `PUT`. You are not required to send entire object representation. |
| `DELETE` | `/api/talks/{id}` | **Not Implemented** Removes a talk. |

<a name="json-api-usage" />
### Using the API

In this scenario, we will submit talks on behalf of a user and we make a few assumptions: we assume that you have **NOT** registered as a Client Application yet and that the user you are submitting talks on behalf of does **NOT** have an account on the target instance of OpenCFP.

#### Register your app as a Client Application with target OpenCFP instance

In order to allow third-party clients to register with a target OpenCFP instance as a Client Application, we support a partial
implementation of the [OAuth 2.0 Dynamic Registration Draft](https://tools.ietf.org/html/draft-ietf-oauth-dyn-reg-23). Specifically,
we implement the ability for an arbitrary client to register itself as a Client Application without any pre-arranged authentication
process (Software Statements / pre-arranged initial Access Token).

If you haven't previously registered a client application and received a `client_id` and `client_secret`, you will need to either
do so manually or have your application do so dynamically before being able to redirect users for authorization. Developers using
the client registration endpoint **should only register once**. It's not going to break anything if you create a new client application
for every single request... but don't do that. It's mean.

To register your application as a Client Application, you will need to send the following request:

```
POST /oauth/register HTTP/1.1
Host: someopencfp.com
Accept: application/json
Content-Type: application/json

{
	"client_name": "Some Custom Web Application"
	"redirect_uri": "https://yourwebapp.com/callback"
}
```

#### Redirect your user to request OpenCFP access

```
GET https://someopencfp.com/oauth/authorize
```

**Parameters**

| Name | Type | Description |
| --- | --- | --- |
| `client_id` | `string` | **Required.** The client identifier you received as part of the client application registration process. |
| `redirect_uri` | `string` | The URL in your application where users will be sent after authorizing access. |
| `scope` | `string` | A comma-separated list of scopes. If not provided, `scope` defaults to an empty list; basically allowing you to authenticate as the user with no authorization to the user's protected resources. |
| `state` | `string` | An unguessable random string used to protect against CSRF attacks. You will send this back when you trade authorization code for access token. |

#### User authenticates or creates new account

A user must authenticate to the target instance of OpenCFP before authorizing access to a Client Application. For users
that do not have a previously created account, they will have the option of creating a new account. When they complete
the account creation process, they will be automatically authenticated into that account and proceed.

**[Example Interface Here]**

#### User authorizes access

After the user authenticates, they are presented with an authorization interface where they can either approve or deny
a Client Application's access to their protected resources. They will see the name of your Client Application in addition
to the OAuth2 scopes you have requested. If the user approves, the flow proceeds to the next step. Otherwise, they are still
redirected to your application without an Authorization Code and you would need to implement some way of handling that.

**[Example Interface Here]**

#### OpenCFP redirects back to your site with Authorization Code

If the user accepts your request, OpenCFP redirects to your site with a `code` query parameter as well as the `state` you provided. If the states do not match, the process should be aborted.

#### Trade Authorization Code for Access Token

**Parameters**

| Name | Type | Description |
| --- | --- | --- |
| `client_id` | `string` | **Required.** The client identifier you received as part of the client application registration process. |
| `client_secret` | `string` | **Required.** The client secret you received as part of the client application registration process. |
| `code` | `string` | **Required.** The code you received as a response when requesting the authorization code. |
| `redirect_url` | `string` | The URL in your application where users will be sent after authorizing access. |

**Response**

``` json
{
	"access_token": "a12834769e4ae7ae178b292c2ee42f710c8316c7",
	"refresh_token": "24710c8316c7c2ee42fa1ae7ae178b292834769e",
	"token_type": "bearer",
	"scope": "public,talks",
	"expires_in": 3600
}
```

#### Use the token to do work on behalf of an OpenCFP user

Once you have obtained an access token, you can do stuff! You will need to provide that access token for every request
you make to restricted API endpoints. All requests **SHOULD** be sent using TLS (if the OpenCFP instance supports it) because
otherwise, we're sending credentials in cleartext.

Access tokens MUST be sent in an `Authorization` header as follows from our example access token above:

```
GET /api/me HTTP/1.1
Host: someopencfp.com
Authorization: Bearer a12834769e4ae7ae178b292c2ee42f710c8316c7
```

> The examples that follow are subject to change. API endpoints and behaviour are still in flux. This should serve more as an example of what to expect as far as interacting with the API, not specifically how the endpoints will work.

**View speaker's profile**

```
GET /api/me HTTP/1.1
Host: someopencfp.com
Accept: application/json
Authorization: Bearer a12834769e4ae7ae178b292c2ee42f710c8316c7
```

```
HTTP/1.1 200 OK
Content-type: application/json

{
	"first_name": "Ham",
	"last_name": "Burglar",
	"email": "hamburglar@someopencfp.com
	"company": "ACME Corporation",
	"twitter": "@hamburglar",
	"bio": "..."
}
```

**Submit a talk**

```
POST /api/talks HTTP/1.1
Host: someopencfp.com
Accept: application/json
Content-Type: application/json
Authorization: Bearer a12834769e4ae7ae178b292c2ee42f710c8316c7

{
	"title": "Sample Talk",
	"description": "...",
	"type": "regular",
	"level": "mid",
	"category": "api"
}
```

```
HTTP/1.1 201 Created
Content-type: application/json

{
	"id": "1"
	"title": "Sample Talk",
	"description": "...",
	"type": "regular",
	"level": "mid",
	"category": "api"
}
```

**Verify talk was submitted**

```
GET /api/talks/1 HTTP/1.1
Host: someopencfp.com
Accept: application/json
Authorization: Bearer a12834769e4ae7ae178b292c2ee42f710c8316c7
```

```
HTTP/1.1 200 OK
Content-type: application/json

{
	"id": "1"
	"title": "Sample Talk",
	"description": "...",
	"type": "regular",
	"level": "mid",
	"category": "api"
}
```

**Delete talk**

```
DELETE /api/talks/1 HTTP/1.1
Host: someopencfp.com
Accept: application/json
Authorization: Bearer a12834769e4ae7ae178b292c2ee42f710c8316c7
```

```
HTTP/1.1 200 OK
```

#### Refresh an access token after it expires

Access tokens have a TTL of one hour. Once expired, you will need to either request another access token or we give you
the ability to refresh access tokens through use of the Refresh Token Grant. Taking advantage of this is actually pretty simple.
You'll basically send a request that looks similar to this:

```
POST /oauth/access_token HTTP/1.1
Host: someopencfp.com
Accept: application/json
Authorization: Bearer a12834769e4ae7ae178b292c2ee42f710c8316c7

grant_type=refresh_token&refresh_token=24710c8316c7c2ee42fa1ae7ae178b292834769e
```

```
HTTP/1.1 200 OK
Content-type: application/json

{
	"access_token": "2ee42f710c8316c7a12834769e4ae7ae178b292c",
	"refresh_token": "1ae7ae178b292834769e24710c8316c7c2ee42fa",
	"token_type": "bearer",
	"scope": "public,talks",
	"expires_in": 3600
}
```

After refreshing the access token, you'll obviously want to update the previous token you've associated with your user.
Also note that **refresh tokens are rotated** in addition to the access token. You'll want to keep track of this per-user.

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

<a name="scripts-rule-all" />
### Scripts to Rule Them All

OpenCFP follows the [Scripts to Rule Them All](https://github.com/github/scripts-to-rule-them-all) pattern. This allows
for an easy to follow convention for common tasks when developing applications.

#### Initial Setup
This command will install all dependencies, run database migrations, and alert you of any missing configs.

```
$ script/setup
```

#### Update Application
This command will update all dependencies and run new migrations

```
$ script/update
```

#### Start Development/Local Server
This command will start a built-in php web server, using port `8000`.

```
$ script/server
```

#### Run Tests
This command will run the PHPUnit test suite using distributed phpunit config, `phpunit.xml.dist`, if
no phpunit.xml is found in the root.

```
$ script/test
```

<a name="testing" />
## Testing

There is a test suite that uses PHPUnit in the /tests directory. To set up
your environment for testing:

1. Create a testing database, and update the name and credentials in
   /config/testing.yml
2. Copy the default `phinx.yml.dist` to `phinx.yml`
3. The recommended way to run the tests is:

```
$ script/test
```

The default phpunit.xml.dist file is in the root directory for the project.

<a name="troubleshooting" />
## Troubleshooting

**I'm getting weird permissions-related errors to do with HTML Purifier.**

You may need to edit directory permissions for some vendor packages such as HTML Purifier. Check the `/cache` directory's
permissions first (if you have `cache.enabled` set to `true`).
