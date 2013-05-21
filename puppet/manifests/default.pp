$mysql_root_password = ''

exec { 'apt-get update' :
    command => 'apt-get update',
    path    => '/usr/bin/',
}

class { 'apt' :
    always_apt_update => true
}

package { ['build-essential', 'python-software-properties'] :
    ensure  => installed,
    require => Exec['apt-get update'],
}

# apt::ppa { 'ppa:ondrej/php5' : }

apt::builddep { 'php5' : }

class { 'apache' :
    # require => Apt::Ppa['ppa:ondrej/php5'],
}

apache::module { 'rewrite' : }

apache::vhost { 'cfp':
    server_name   => 'cfp.dev',
    serveraliases => ['www.cfp.dev'],
    docroot       => '/home/vagrant/shared/web',
    port          => '80',
    priority      => '1',
}

exec {'/usr/sbin/a2dissite default': 
    require => Apache::Vhost['cfp'],
}

class { 'php' :
    service => 'apache',
    require => Package['apache'],
}

php::module { 'cli' : }
php::module { 'curl' : }
php::module { 'intl' : }
php::module { 'mcrypt' : }
php::module { 'mysql' : }

class { 'php::pear' :
    require => Class['php'],
}

class { 'php::devel' :
    require => Class['php'],
}

php::pecl::module { 'pecl_http' :
    use_package => false,
}

class { 'xdebug' :
    require => Package['php'],
    notify  => Service['apache'],
}

xdebug::config { 'default' :
    default_enable        => '1',
    remote_autostart      => '1',
    remote_connect_back   => '1',
    remote_enable         => '1',
    remote_handler        => 'dbgp',
    remote_port           => '9000',
    show_local_vars       => '0',
    var_display_max_data  => '10000',
    var_display_max_depth => '20',
    show_exception_trace  => '0'
}

# php::custom::xhprof { 'xhprof' :
#     output_dir => '/var/www/xhprof',
#     require    => Class['php'],
# }

class { 'mysql': }
class { 'mysql::server':
  config_hash => { 'root_password' => $mysql_root_password }
}

database { "cfp":
    ensure => "present",
    charset => "utf8"
}

exec { "seed_data":
    command => "/bin/cat /vagrant/schema/mysql.sql | /usr/bin/mysql -u root cfp"
}
