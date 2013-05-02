# Define: php::pecl::module
#
# Installs the defined php pecl component
#
# == Parameters
#
# [*service_autorestart*]
#   wathever we want a module installation notify a service to restart.
#
# [*service*]
#   Service to restart.
#
# [*use_package*]
#   Tries to install pecl module with the relevant package.
#   If set to "no" it installs the module via pecl command. Default: true
#
# [*preferred_state*]
#   Define which preferred state to use when installing Pear modules via pecl
#   command line (when use_package=no). Default: true
#
# [*auto_answer*]
#   The answer(s) to give to pecl prompts for unattended install
#
# [*verbose*]
#   (Optional) - If you want to see verbose pecl output during installation.
#   This can help to debug installation problems (missing packages) during
#   installation process. Default: false
#
# == Examples
# php::pecl::module { 'intl': }
#
# This will install xdebug from pecl source instead of using the package
#
# php::pecl::module { 'xdebug':.
#   use_package => "no",
# }
#
define php::pecl::module (
  $service_autorestart = $php::bool_service_autorestart,
  $service             = $php::service,
  $use_package         = 'yes',
  $preferred_state     = 'stable',
  $auto_answer         = '\\n',
  $ensure              = present,
  $verbose             = false ) {

  include php
  include php::devel

  $manage_service_autorestart = $service_autorestart ? {
    true    => $service ? {
      ''      => undef,
      default => "Service[$service]",
    },
    false   => undef,
  }

  case $use_package {
    yes: {
      package { "php-${name}":
        name => $operatingsystem ? {
          ubuntu  => "php5-${name}",
          debian  => "php5-${name}",
          default => "php-${name}",
          },
        ensure => $ensure,
        notify => $manage_service_autorestart,
      }
    }
    default: {

      $bool_verbose = any2bool($verbose)

      $pecl_real_logoutput = $bool_verbose ? {
        true  => true,
        false => undef,
      }

      exec { "pecl-${name}":
        command   => "/usr/bin/printf \"${auto_answer}\" | /usr/bin/pecl -d preferred_state=${preferred_state} install ${name}",
        unless    => "/usr/bin/pecl info ${name}",
        logoutput => $pecl_real_logoutput,
        require   => [ Class['php::pear'], Class['php::devel']],
        #FIXME: Implement ensure => absent,
      }
      if $php::bool_augeas == true {
        php::augeas {
          "augeas-${name}":
            entry  => "PHP/extension[. = \"${name}.so\"]",
            value  => "${name}.so",
            ensure => $ensure,
            notify => $manage_service_autorestart,
        }
      }
    }
  } # End Case
}
