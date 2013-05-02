define php::custom::xhprof ($output_dir = '/var/tmp/xhprof')
{
    if !defined(File[$output_dir]) {
        file { $output_dir :
            ensure => 'directory',
        }
    }

    git::repo{ 'xhprof' :
        path   => "${settings::confdir}/files/git/xhprof",
        source => 'git://github.com/facebook/xhprof.git'
    }

    build::install { 'xhprof' :
        folder       => "${settings::confdir}/files/git/xhprof/extension",
        buildoptions => "/usr/bin/phpize && ./configure",
        require      => [
            Git::Repo['xhprof'],
            Apt::Builddep['php5']
        ],
    }

    php::ini { 'xhprof' :
        value    => [
            '[xhprof]',
            'extension=xhprof.so',
            "xhprof.output_dir=\"${output_dir}\""
        ],
        template => 'extra-ini.erb',
        target   => 'xhprof.ini',
    }
}
