require 'spec_helper'

describe 'mysql::java' do

  describe 'on a debian based os' do
    let :facts do
      { :osfamily => 'Debian'}
    end
    it { should contain_package('mysql-connector-java').with(
      :name   => 'libmysql-java',
      :ensure => 'present'
    )}
  end

  describe 'on a freebsd based os' do
    let :facts do
      { :osfamily => 'FreeBSD'}
    end
    it { should contain_package('mysql-connector-java').with(
      :name   => 'databases/mysql-connector-java',
      :ensure => 'present'
    )}
  end

  describe 'on a redhat based os' do
    let :facts do
      {:osfamily => 'Redhat'}
    end
    it { should contain_package('mysql-connector-java').with(
      :name   => 'mysql-connector-java',
      :ensure => 'present'
    )}
    describe 'when parameters are supplied' do
      let :params do
        {:package_ensure => 'latest', :package_name => 'java-mysql'}
      end
      it { should contain_package('mysql-connector-java').with(
        :name   => 'java-mysql',
        :ensure => 'latest'
      )}
    end
  end

  describe 'on any other os' do
    let :facts do
      {:osfamily => 'foo'}
    end

    it 'should fail' do
      expect { subject }.to raise_error(/Unsupported osfamily: foo/)
    end
  end

end
