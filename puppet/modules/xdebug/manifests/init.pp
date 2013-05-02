class xdebug {
    package { 'xdebug':
        name   => 'php5-xdebug',
        ensure  => installed,
    }
}
