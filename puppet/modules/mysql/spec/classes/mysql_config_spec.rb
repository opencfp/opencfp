require 'spec_helper'
describe 'mysql::config' do

  let :constant_parameter_defaults do
    {
     :root_password     => 'UNSET',
     :old_root_password => '',
     :bind_address      => '127.0.0.1',
     :port              => '3306',
     :etc_root_password => false,
     :datadir           => '/var/lib/mysql',
     :default_engine    => 'UNSET',
     :ssl               => false,
    }
  end

  describe 'with osfamily specific defaults' do
    {
      'Debian' => {
         :datadir      => '/var/lib/mysql',
         :service_name => 'mysql',
         :config_file  => '/etc/mysql/my.cnf',
         :socket       => '/var/run/mysqld/mysqld.sock',
         :pidfile      => '/var/run/mysqld/mysqld.pid',
         :root_group   => 'root',
         :ssl_ca       => '/etc/mysql/cacert.pem',
         :ssl_cert     => '/etc/mysql/server-cert.pem',
         :ssl_key      => '/etc/mysql/server-key.pem'
      },
      'FreeBSD' => {
         :datadir      => '/var/db/mysql',
         :service_name => 'mysql-server',
         :config_file  => '/var/db/mysql/my.cnf',
         :socket       => '/tmp/mysql.sock',
         :pidfile      => '/var/db/mysql/mysql.pid',
         :root_group   => 'wheel',
      },
      'Redhat' => {
         :datadir      => '/var/lib/mysql',
         :service_name => 'mysqld',
         :config_file  => '/etc/my.cnf',
         :socket       => '/var/lib/mysql/mysql.sock',
         :pidfile      => '/var/run/mysqld/mysqld.pid',
         :root_group   => 'root',
         :ssl_ca       => '/etc/mysql/cacert.pem',
         :ssl_cert     => '/etc/mysql/server-cert.pem',
         :ssl_key      => '/etc/mysql/server-key.pem'
      }
    }.each do |osfamily, osparams|


      describe "when osfamily is #{osfamily}" do

        let :facts do
          {:osfamily => osfamily}
        end

        describe 'when root password is set' do

          let :params do
           {:root_password => 'foo'}
          end

          it { should contain_exec('set_mysql_rootpw').with(
            'command'   => 'mysqladmin -u root  password \'foo\'',
            'logoutput' => true,
            'unless'    => "mysqladmin -u root -p\'foo\' status > /dev/null",
            'path'      => '/usr/local/sbin:/usr/bin:/usr/local/bin'
          )}

          it { should contain_file('/root/.my.cnf').with(
            'content' => "[client]\nuser=root\nhost=localhost\npassword=foo\n",
            'require' => 'Exec[set_mysql_rootpw]'
          )}

        end

        describe 'when root password and old password are set' do
          let :params do
           {:root_password => 'foo', :old_root_password => 'bar'}
          end

          it { should contain_exec('set_mysql_rootpw').with(
            'command'   => 'mysqladmin -u root -p\'bar\' password \'foo\'',
            'logoutput' => true,
            'unless'    => "mysqladmin -u root -p\'foo\' status > /dev/null",
            'path'      => '/usr/local/sbin:/usr/bin:/usr/local/bin'
          )}

        end

        [
          {},
          {
            :service_name   => 'dans_service',
            :config_file    => '/home/dan/mysql.conf',
            :service_name   => 'dans_mysql',
            :pidfile        => '/home/dan/mysql.pid',
            :socket         => '/home/dan/mysql.sock',
            :bind_address   => '0.0.0.0',
            :port           => '3306',
            :datadir        => '/path/to/datadir',
            :default_engine => 'InnoDB',
            :ssl            => true,
            :ssl_ca         => '/path/to/cacert.pem',
            :ssl_cert       => '/path/to/server-cert.pem',
            :ssl_key        => '/path/to/server-key.pem'
          }
        ].each do |passed_params|

          describe "with #{passed_params == {} ? 'default' : 'specified'} parameters" do

            let :parameter_defaults do
              constant_parameter_defaults.merge(osparams)
            end

            let :params do
              passed_params
            end

            let :param_values do
              parameter_defaults.merge(params)
            end

            it { should contain_exec('mysqld-restart').with(
              :refreshonly => true,
              :path        => '/sbin/:/usr/sbin/:/usr/bin/:/bin/',
              :command     => "service #{param_values[:service_name]} restart"
            )}

            it { should_not contain_exec('set_mysql_rootpw') }

            it { should contain_file('/root/.my.cnf')}

            it { should contain_file('/etc/mysql').with(
              'owner'  => 'root',
              'group'  => param_values[:root_group],
              'notify' => 'Exec[mysqld-restart]',
              'ensure' => 'directory',
              'mode'   => '0755'
            )}
            it { should contain_file('/etc/mysql/conf.d').with(
              'owner'  => 'root',
              'group'  => param_values[:root_group],
              'notify' => 'Exec[mysqld-restart]',
              'ensure' => 'directory',
              'mode'   => '0755'
            )}
            it { should contain_file(param_values[:config_file]).with(
              'owner'  => 'root',
              'group'  => param_values[:root_group],
              'notify' => 'Exec[mysqld-restart]',
              'mode'   => '0644'
            )}
            it 'should have a template with the correct contents' do
              content = param_value(subject, 'file', param_values[:config_file], 'content')
              expected_lines = [
                "port    = #{param_values[:port]}",
                "socket    = #{param_values[:socket]}",
                "pid-file  = #{param_values[:pidfile]}",
                "datadir   = #{param_values[:datadir]}",
                "bind-address    = #{param_values[:bind_address]}"
              ]
              if param_values[:default_engine] != 'UNSET'
                expected_lines = expected_lines | [ "default-storage-engine = #{param_values[:default_engine]}" ]
              end
              if param_values[:ssl]
                expected_lines = expected_lines |
                  [
                    "ssl-ca    = #{param_values[:ssl_ca]}",
                    "ssl-cert  = #{param_values[:ssl_cert]}",
                    "ssl-key   = #{param_values[:ssl_key]}"
                  ]
              end
              (content.split("\n") & expected_lines).should == expected_lines
            end
          end
        end
      end
    end
  end

  describe 'when etc_root_password is set with password' do

    let :facts do
      {:osfamily => 'Debian'}
    end

    let :params do
     {:root_password => 'foo', :old_root_password => 'bar', :etc_root_password => true}
    end

    it { should contain_exec('set_mysql_rootpw').with(
      'command'   => 'mysqladmin -u root -p\'bar\' password \'foo\'',
      'logoutput' => true,
      'unless'    => "mysqladmin -u root -p\'foo\' status > /dev/null",
      'path'      => '/usr/local/sbin:/usr/bin:/usr/local/bin'
    )}

    it { should contain_file('/root/.my.cnf').with(
      'content' => "[client]\nuser=root\nhost=localhost\npassword=foo\n",
      'require' => 'Exec[set_mysql_rootpw]'
    )}

  end

  describe 'setting etc_root_password should fail on redhat' do
    let :facts do
      {:osfamily => 'Redhat'}
    end

    let :params do
     {:root_password => 'foo', :old_root_password => 'bar', :etc_root_password => true}
    end

    it 'should fail' do
      expect { subject }.to raise_error(Puppet::Error, /Duplicate (declaration|definition)/)
    end

  end

  describe 'unset ssl params should fail when ssl is true on freebsd' do
    let :facts do
      {:osfamily => 'FreeBSD'}
    end

    let :params do
     { :ssl => true }
    end

    it 'should fail' do
      expect { subject }.to raise_error(Puppet::Error, /required when ssl is true/)
    end

  end

end
