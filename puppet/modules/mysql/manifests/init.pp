# Class: mysql
#
#   This class installs mysql client software.
#
# Parameters:
#   [*client_package_name*]  - The name of the mysql client package.
#
# Actions:
#
# Requires:
#
# Sample Usage:
#
class mysql (
  $package_name   = $mysql::params::client_package_name,
  $package_ensure = 'present'
) inherits mysql::params {

  package { 'mysql_client':
    ensure => $package_ensure,
    name   => $package_name,
  }

}
