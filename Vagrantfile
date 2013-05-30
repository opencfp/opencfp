Vagrant::Config.run do |config|
    config.vm.box     = "precise64"
    config.vm.box_url = "http://files.vagrantup.com/precise64.box"

    config.vm.host_name = "cfp.dev"
    config.vm.network :hostonly, "10.99.99.10", :auto_config => true
    config.ssh.forward_agent = true

    Vagrant.configure("1") do |config|
        config.vm.customize ["modifyvm", :id, "--memory", 768]
        config.vm.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
    end

    Vagrant.configure("2") do |config|
        config.vm.provider :virtualbox do |vb|
            config.vm.customize ["modifyvm", :id, "--memory", 768]
            config.vm.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
        end
    end

    config.vm.share_folder "shared", "/home/vagrant/shared", ".", {:nfs => true}

    config.vm.provision :puppet do |puppet|
        puppet.manifests_path = "puppet/manifests"
        puppet.module_path = "puppet/modules"
        puppet.options = [
            '--verbose',
            '--environment=dev',
        ]
    end
end
