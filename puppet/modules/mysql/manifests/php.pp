# Class: mysql::php
#
# This class installs the php libs for mysql.
#
# Parameters:
#   [*ensure*]   - ensure state for package.
#                  can be specified as version.
#   [*packagee*] - name of package
#
class mysql::php(
  $package_name   = $mysql::params::php_package_name,
  $package_ensure = 'present'
) inherits mysql::params {

  package { 'php-mysql':
    ensure => $package_ensure,
    name   => $package_name,
  }

}
