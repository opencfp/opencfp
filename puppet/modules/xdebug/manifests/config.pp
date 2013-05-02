define xdebug::config (
    #Template variables
    $ini_file_path    = '',
    $default_enable   = '',
    $remote_autostart = '',
    $remote_connect_back = '',
    $remote_enable    = '',
    $remote_handler   = '',
    $remote_host      = '',
    $remote_port      = '',
    $show_exception_trace = '',
    $show_local_vars  = '',
    $var_display_max_data = '',
    $var_display_max_depth = '',
  )
{
    #Template variables default values
    $xdebug_ini_file_path = $ini_file_path ? {
        ''      => '/etc/php5/conf.d/xdebug_config.ini',
        default => $ini_file_path,
    }

    $xdebug_default_enable = $default_enable ? {
        ''      => '1',
        default => $default_enable,
    }

    $xdebug_remote_connect_back = $remote_connect_back ? {
        ''      => '0',
        default => $remote_connect_back,
    }

    $xdebug_remote_enable = $remote_enable ? {
        ''      => '1',
        default => $remote_enable,
    }

    $xdebug_remote_handler = $remote_handler ? {
        ''      => 'dbgp',
        default => $remote_handler,
    }

    $xdebug_remote_host = $remote_host ? {
        ''      => 'localhost',
        default => $remote_host,
    }

    $xdebug_remote_port = $remote_port ? {
        ''      => '9000',
        default => $remote_port,
    }

    $xdebug_remote_autostart = $remote_autostart ? {
        ''      => '1',
        default => $remote_autostart,
    }

    $xdebug_show_local_vars = $show_local_vars ? {
        ''      => '1',
        default => $show_local_vars,
    }

    $xdebug_var_display_max_data = $var_display_max_data ? {
        ''      => '5000',
        default => $var_display_max_data,
    }

    $xdebug_var_display_max_depth = $var_display_max_depth ? {
        ''      => '10',
        default => $var_display_max_depth,
    }

    $xdebug_show_exception_trace = $show_exception_trace ? {
        ''      => '1',
        default => $show_exception_trace,
    }

    file { "$xdebug_ini_file_path" :
        content => template('xdebug/ini_file.erb'),
        ensure  => present,
        require => Package['xdebug'],
    }
}
