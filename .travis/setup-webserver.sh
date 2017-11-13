# Update packages
sudo apt-get update > /dev/null

# Install server packages
sudo apt-get install -y --force-yes apache2 libapache2-mod-fastcgi postfix

# Configure PHP-FPM.
sudo cp ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf.default ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf
echo "cgi.fix_pathinfo = 1" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
sudo a2enmod rewrite actions fastcgi alias
~/.phpenv/versions/$(phpenv version-name)/sbin/php-fpm

# Set up default virtual host
sudo cp -f .travis/travis-ci-apache /etc/apache2/sites-available/default
sudo sed -e "s?%TRAVIS_BUILD_DIR%?$(pwd)?g" --in-place /etc/apache2/sites-available/default
sudo service apache2 restart
