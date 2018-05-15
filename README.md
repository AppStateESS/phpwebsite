Welcome to Canopy!
(previously phpWebSite)
======================

[![Build Status](https://travis-ci.org/AppStateESS/phpwebsite.png?branch=master)](https://travis-ci.org/AppStateESS/phpwebsite)

* [phpWebSite Home](https://phpwebsite.appstate.edu)
* [phpWebSite on Github](https://github.com/AppStateESS/phpwebsite)

phpWebSite comes with its own set of Pear files. Most of the time you
should just use the files included with distribution.

**phpWebsite is now using composer for dependencies. Before using the 
web installer you will have to install composer and run composer install
from the project root directory.**

**The default theme (bootstrap) requires these processes to be run in the
themes/bootstrap directory
npm install
npm run prod


After installation you will need to install some content modules.

1. Login and go to the Administration tab.
2. Click on Boost.
3. Click on Other Modules
4. Install any of the modules included. We recommend you try them all.

**Remember after you install to restrict your directory permissions.**

These directories should always be writable and executable by Apache:
* files/
* images/
* logs/

Additional permission suggestions:
* logs/     should NOT be world readable
* setup/    should be moved or made unreadable AFTER an installation
* convert/  should be removed after converting

**Before updating the core, make sure to make a copy of your
config/core directory or some of your settings could get
overwritten.**

If you are upgrading, the only file you must keep in your config/core
directory is language.php.

Canopy uses UTF-8 character encoding. Some older database versions
of MySQL (pre 4.1) do not support it.

Docker
======
Canopy can now be run in a Docker Container!

1. Install Docker Engine as per instructions. * [Docker Install](https://docs.docker.com/engine/installation/)
2. Install Docker Compose as per instructions. *[Docker Compose Install](https://docs.docker.com/compose/install/)
3. Run docker-compose up -d. ( the d option runs it in the background )

You can run psql and mysql command line from localhost to access the container db's. Just use port 5432 for postgres and 4306 for mysql.

(Depending on your system you may need to add your user to the docker group in order to have the privileges to run the docker-compose command)

**Remember while setting up Canopy that your database location will not be localhost. We are running 3 seperate containers. One for web, one for mysql, and one for postgresql. If you want mysql then the host will be mysql_db otherwise postgres host will be postgres_db.

Vagrant
=======
Canopy can now be run in a Vagrant Box!

1. Install VirtualBox and Vagrant as per instructions for your platform
2. Create a new Vagrant Box called 'centos64' from the URL
   http://puppet-vagrant-boxes.puppetlabs.com/centos-64-x64-vbox4210.box
   on Linux or Mac, this is done like so:
   ```vagrant box add centos64 http://puppet-vagrant-boxes.puppetlabs.com/centos-64-x64-vbox4210.box```
3. ```vagrant up```!

Flowplayer
==========
Canopy includes the free version of Flowplayer. Commercial web sites are required to purchase a contract from Flowplayer.
Please read more at https://flowplayer.org/pricing/
