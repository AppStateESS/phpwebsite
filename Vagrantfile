# -*- mode: ruby -*-
# vi: set ft=ruby :

# Configuration for Vagrant.  NOT DONE YET!

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.vm.box = "centos64"
  config.vm.box_url = "http://puppet-vagrant-boxes.puppetlabs.com/centos-64-x64-vbox4210.box"
  config.vm.provision :shell, :path => "bootstrap.sh"
  config.vm.network :forwarded_port, host: 7970, guest: 80
  config.vm.network :forwarded_port, host: 7971, guest: 3306
  config.vm.network :forwarded_port, host: 7972, guest: 5432
end
