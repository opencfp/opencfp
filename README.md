opencfp
=======

Repo for OpenCFP project, a PHP-based conference talk submission system

Contributing
------------

We welcome and love contributions! To facilitate this we encourage you to create 
a new personal branch after you fork this repo, for content and changes specific to your event. 
However, anything you are willing to push back should be updated in the master branch. This will
help keep the master branch generic for future event organizers. You would then be able to 
merge master to your private branch and get updates when desired.

Requirements
------------

Please see the [composer.json](composer.json) file.
You may need to install php5-intl extension for PHP. (on Ubuntu, not sure what it is called for other OS)
Also, must have PHP 5.3.3+.

Installation
------------

### Main Setup

#### Clone project

1. Clone this project into your working directory.

#### Use Composer to get dependencies

2. Now tell composer to download dependencies by running the command:
NOTE: May need to download composer.phar first from http://getcomposer.org

    ```bash
    $ php composer.phar install
    ```
3. Set up your desired webserver to point to the '/web' directory.

4. Create database along with user/password in MySQL for application to use.

5. Rename the /config/config.development.ini.dist file to /config/config.development.ini.

    ```bash
    $ mv /config/config.development.ini.dist /config/config.development.ini
    $ mv /config/config.production.ini.dist /config/config.production.ini
    ```
NOTE: Use development or production naming as appropriate.

6. Customize /config/config.development.ini as needed for your environment and site settings.

    NOTE: The enddate will be observed. The app now locks at 11:59pm on the given enddate.

7. Alter the /classes/OpenCFP/Bootstrap.php file with the desired $environment. Lines 11 and 12.

8. Populate MySQL database by using the mysql.sql script available in /schema folder.

9. May need to edit directory permissions for some of the vendor packages. (your mileage may vary)

    NOTE: We're looking at you ezyang htmlpurifier.

10. Update directory permissions to allow for headshot upload.

    /web/uploads - needs to be writable by the web server

11. May need to alter the memory limit of your web server to allow image manipulation of headshots.

    NOTE: This is largely dictated by the size of the images people upload. Typically 512M works.
    If you find that 'speakers' table is not being populated, this may be why.

12. Customize templates and /web/assets/css/site.css to your hearts content.

13. Enjoy!!!


Additional Admin Setup
----------------------

1. There is also a script available in /tools directory (to be called via command line) To enable a user to become an Admin.  So via CLI from within the /tools directory.

    ```bash
    $ php create_admin_user.php --update {email-address}
    ```
This will enable specified user to navigate to /admin through a link now visible on the Dashboard.

Testing
-------

There is a test suite that uses PHPUnit in the /tests directory. The recommended way to run the tests is:

    ```bash
    $ cd tests
    $ ../vendor/bin/phpunit
    ```
[![Build Status](https://travis-ci.org/chartjes/opencfp.svg?branch=master)](https://travis-ci.org/chartjes/opencfp)


Contributions Welcome
---------------------

The project can only get better with contributions from people. See something wrong? Have an idea for a new feature? Send a pull request and let's talk about it.
