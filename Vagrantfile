# -*- mode: ruby -*-
# vi: set ft=ruby :

# We need to run composer install before we do anything
if !File.directory?("./vendor")
  puts "Please run `composer install` before running `vagrant up`"
  exit
end


Vagrant.configure(2) do |config|
  config.vm.box = "ubuntu/trusty64"

  # Set up our network
  config.vm.hostname = "opencfp.dev"
  config.vm.network "private_network", ip: "192.168.33.10"

  # Shared folders
  config.vm.synced_folder ".", "/var/www/opencfp"

  # Manage /etc/hosts with vagrant-hostmanager
  if Vagrant.has_plugin?("vagrant-hostmanager")
    config.hostmanager.enabled = true
    config.hostmanager.manage_host = true
  else
    puts "### Important: This project uses vagrant-hostmanager. Please"
    puts "### run `vagrant plugin install vagrant-hostmanager` to install"
    puts "### it."
  end

  # Provision with Ansible
  config.vm.provision "ansible" do |ansible|
      ansible.playbook = "ansible/playbook.yml"
  end


end


