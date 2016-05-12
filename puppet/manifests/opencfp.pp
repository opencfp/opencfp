package { 'apache2':
  ensure => present
}

package { 'php5':
  ensure => present
}

package { 'php5-intl':
  ensure => present
}

package { 'php5-mysql':
  ensure => present
}

package { 'php5-mysqlnd':
  ensure => present
}

package { 'php5-apcu':
  ensure => present
}

package { 'php5-sqlite':
  ensure => present
}

package { 'php5-xdebug':
  ensure => present
}

package { 'php5-xhprof':
  ensure => present
}

package { 'mysql-server':
  ensure => present
}

service { 'apache2':
  ensure => 'running',
  enable => 'true',
  require => Package['apache2'],
}

service { 'mysql':
  ensure => 'running',
  enable => 'true',
  require => Package['mysql-server'],
}

file { '/etc/apache2/sites-available/opencfp.conf':
  owner => 'root',
  group => 'root',
  mode => '0644',
  replace => 'no',
  content => template('opencfp/apache2_config.erb'),
  notify => Service[apache2]
}

file { '/vagrant/config/development.yml':
  replace => 'no',
  source => '/vagrant/config/development.dist.yml'
}

file { '/vagrant/web/.htaccess':
  replace => 'no',
  source => '/vagrant/web/htaccess.dist'
}

exec { 'a2ensite':
  path => [ '/bin', '/usr/bin', '/usr/sbin' ],
  command => 'a2ensite opencfp',
  require => [
    File['/etc/apache2/sites-available/opencfp.conf'],
    Package[apache2]
  ]
}

exec { 'a2enmod-rewrite':
  path => [ '/bin', '/usr/bin', '/usr/sbin' ],
  command => 'a2enmod rewrite',
  require => Package[apache2]
}

exec { 'database':
  path => [ '/bin', '/usr/bin', '/usr/sbin' ],
  command => 'mysql -u root -e "CREATE DATABASE IF NOT EXISTS cfp;"'
}

exec { 'migrations':
  cwd => '/vagrant',
  path => [ '/bin', '/usr/bin', '/usr/sbin' ],
  command => 'php ./vendor/bin/phinx migrate',
  require => Exec[database]
}
