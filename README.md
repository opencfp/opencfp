opencfp
=======

Repo for OpenCFP project, a PHP-based conference talk submission system

Requirements
------------

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

5. Rename the /config/config.ini.dist file to /config/config.ini.

    ```bash
    $ mv /config/config.ini.dist /config/config.ini
    ```

6. Customize /config/config.ini as needed for your environment and site settings.

7. Populate MySQL database by using the mysql.sql script available in /schema folder.

8. May need to edit directory permissions for some of the packages. (your mileage may vary)

9. Customize templates and /web/assets/css/site.css to your hearts content.

10. Enjoy!!!


Additional Admin Setup
----------------------

There is also a script available in /tools directory (to be called via command line)
To enable a user to become an Admin.  So from within the /tools directory:

    ```bash
    $ php create_admin_user.php update {email-address}
    ```
This will enable that user to navigate to /admin/talks through a link now visible on the Dashboard.

Testing
-------

More to come on this.
