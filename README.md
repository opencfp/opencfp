opencfp
=======

Repo for OpenCFP project, a PHP-based conference talk submission system

After cloning and getting the composer.phar I ran composer install and was presented with "Your requirements could not be resolved to an installable set of packages." because the Symfony/icu package requires lib-icu to be installed on the system running it.

    Resolved by installing php5-intl extension for PHP. (on Ubuntu)
    Composer install went well after that.

Set up Apache to have vhost pointing to project root and did hosts file to use dev domain locally.
Navigating in a browser to the /web directory after the install I received an error about the Config file missing.

    Resolved by renaming the /config/config.ini.dist to /config/config.ini, then updating the contents of the ini file to match environment.
    This highlighted that I needed to set up the DB, so I created the cfp database. (don't see a sql file anywhere, so hope the app builds schema on initial load.)
    Site loads now, but ugly.

Realized that I should have set up Apache to use /web as the document root, so edited that and now get styles.
Try to add first speaker:

    Oops, /vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer directory needs to be writeable.
    Now I just get the error message that something went wrong, but no details.

Found /schema/mysql.sql file and used it to create tables in the DB.
Now all seems functional, YAY!