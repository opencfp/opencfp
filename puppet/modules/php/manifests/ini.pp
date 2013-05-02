define php::ini (
    $value    = '',
    $template = 'extra-ini.erb',
    $target   = 'extra.ini'
) {
    file { "/etc/php5/conf.d/${target}" :
        ensure  => 'present',
        content => template("php/${template}"),
        require => Package['php'],
        notify  => Service['apache'],
    }

    file { "/etc/php5/cli/conf.d/${target}" :
        ensure  => 'present',
        content => template("php/${template}"),
        require => Package['php'],
    }
}
