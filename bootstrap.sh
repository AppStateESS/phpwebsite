#!/usr/bin/env bash

# This is a work-in-progress provisioning script for Vagrant.
# It's not done yet.  Don't use it.

# Configuration
DBUSER=phpwebsite
DBPASS=phpwebsite
DBNAME=phpwebsite

CONFIG=/var/phpws/config
FILES=/var/phpws/files
IMAGES=/var/phpws/images
LOGS=/var/phpws/logs

echo "================"
echo "PHPWEBSITE 1.9.x"
echo "================"

echo "==================="
echo "Installing Packages"
echo "==================="
yum -y install http://download.fedoraproject.org/pub/epel/6/x86_64/epel-release-6-8.noarch.rpm
yum -y install httpd php-cli php-pgsql php-pecl-xdebug php-pdo php php-mbstring php-common php-mysql php-soap php-gd php-xml php-pecl-apc mysql-server mysql postgresql-server postgresql phpmyadmin phpPgAdmin

echo "================"
echo "Setting up MySQL"
echo "================"
cat << mySQL > /etc/my.cnf
[mysqld]
datadir=/var/lib/mysql
socket=/var/lib/mysql/mysql.sock
user=mysql
symbolic-links=0
bind-address=0.0.0.0

[mysqld_safe]
log-error=/var/log/mysqld.log
pid-file=/var/run/mysqld/mysqld.pid
mySQL
service mysqld start
mysql -u root <<MySQL
CREATE DATABASE $DBNAME;
GRANT ALL ON $DBNAME.* TO $DBUSER@localhost IDENTIFIED BY '$DBPASS';
GRANT ALL ON *.* TO root@'%';
MySQL

echo "====================="
echo "Setting up PostgreSQL"
echo "====================="
service postgresql initdb
cat << pgSQL > /var/lib/pgsql/data/pg_hba.conf
local phpwebsite phpwebsite           trust
host  all        postgres   0.0.0.0/0 trust
local all        postgres             trust
pgSQL
echo "listen_addresses = '*'" >> /var/lib/pgsql/data/postgresql.conf
service postgresql start
echo -e 'phpwebsite\nphpwebsite' | su - postgres -c 'createuser -SDREP phpwebsite'
su - postgres -c 'createdb -E utf8 -O phpwebsite phpwebsite'

echo "==========================="
echo "Configuring php[My|Pg]Admin"
echo "==========================="
cat << 'PGADMIN' > /etc/httpd/conf.d/phpPgAdmin.conf
Alias /phpPgAdmin /usr/share/phpPgAdmin
<Location /phpPgAdmin>
    Order deny,allow
    Allow from all
</Location>
PGADMIN
cat << 'PGADMINCFG' > /etc/phpPgAdmin/config.inc.php
<?php
$conf['servers'][0]['desc'] = 'PostgreSQL on phpWebSite Vagrant';
$conf['servers'][0]['port'] = 5432;
$conf['servers'][0]['sslmode'] = 'allow';
$conf['servers'][0]['defaultdb'] = 'template1';
$conf['servers'][0]['pg_dump_path'] = '/usr/bin/pg_dump';
$conf['servers'][0]['pg_dumpall_path'] = '/usr/bin/pg_dumpall';
$conf['servers'][0]['slony_support'] = false;
$conf['servers'][0]['slony_sql'] = '/usr/share/pgsql';
$conf['default_lang'] = 'auto';
$conf['autocomplete'] = 'default on';
$conf['extra_login_security'] = false;
$conf['owned_only'] = false;
$conf['show_comments'] = true;
$conf['show_advanced'] = false;
$conf['show_system'] = false;
$conf['show_reports'] = true;
$conf['reports_db'] = 'phppgadmin';
$conf['reports_schema'] = 'public';
$conf['reports_table'] = 'ppa_reports';
$conf['owned_reports_only'] = false;
$conf['min_password_length'] = 1;
$conf['left_width'] = 200;
$conf['theme'] = 'default';
$conf['show_oids'] = false;
$conf['max_rows'] = 30;
$conf['max_chars'] = 50;
$conf['use_xhtml_strict'] = false;
$conf['help_base'] = 'http://www.postgresql.org/docs/%s/interactive/';
$conf['ajax_refresh'] = 3;
$conf['version'] = 19;
?>
PGADMINCFG
cat << 'MYADMIN' > /etc/httpd/conf.d/phpMyAdmin.conf
Alias /phpMyAdmin /usr/share/phpMyAdmin
<Location /phpMyAdmin>
    Order deny,allow
    Allow from all
</Location>
MYADMIN
cat << 'MYADMINCFG' > /etc/phpMyAdmin/config.inc.php
<?php
$cfg['blowfish_secret'] = '';
$i = 0;
$i++;
$cfg['Servers'][$i]['host']          = 'localhost';
$cfg['Servers'][$i]['port']          = '';
$cfg['Servers'][$i]['socket']        = '';
$cfg['Servers'][$i]['connect_type']  = 'tcp';
$cfg['Servers'][$i]['extension']     = 'mysqli';
$cfg['Servers'][$i]['compress']      = FALSE;
$cfg['Servers'][$i]['controluser']   = '';
$cfg['Servers'][$i]['controlpass']   = '';
$cfg['Servers'][$i]['auth_type']     = 'http';
$cfg['Servers'][$i]['user']          = '';
$cfg['Servers'][$i]['password']      = '';
$cfg['Servers'][$i]['only_db']       = '';
$cfg['Servers'][$i]['hide_db']       = '';
$cfg['Servers'][$i]['verbose']       = '';
$cfg['Servers'][$i]['pmadb']         = '';
$cfg['Servers'][$i]['bookmarktable'] = '';
$cfg['Servers'][$i]['relation']      = '';
$cfg['Servers'][$i]['table_info']    = '';
$cfg['Servers'][$i]['table_coords']  = '';
$cfg['Servers'][$i]['pdf_pages']     = '';
$cfg['Servers'][$i]['column_info']   = '';
$cfg['Servers'][$i]['history']       = '';
$cfg['Servers'][$i]['verbose_check'] = TRUE;
$cfg['Servers'][$i]['AllowRoot']     = TRUE;
$cfg['Servers'][$i]['AllowDeny']['order'] = '';
$cfg['Servers'][$i]['AllowDeny']['rules'] = array();
$cfg['Servers'][$i]['AllowNoPassword'] = TRUE;
$cfg['Servers'][$i]['designer_coords'] = '';
$cfg['Servers'][$i]['bs_garbage_threshold'] = 50;
$cfg['Servers'][$i]['bs_repository_threshold'] = '32M';
$cfg['Servers'][$i]['bs_temp_blob_timeout'] = 600;
$cfg['Servers'][$i]['bs_temp_log_threshold'] = '32M';
$cfg['UploadDir'] = '/var/lib/phpMyAdmin/upload';
$cfg['SaveDir']   = '/var/lib/phpMyAdmin/save';
$cfg['PmaNoRelation_DisableWarning'] = TRUE;
?>
MYADMINCFG

echo "=================="
echo "Configuring Xdebug"
echo "=================="
cat << XDEBUG > /etc/php.d/xdebug.ini
zend_extension=/usr/lib64/php/modules/xdebug.so
xdebug.remote_enable=1
xdebug.remote_handler=dbgp
xdebug.remote_host=10.0.2.2
xdebug.remote_port=9000
xdebug.remote_autostart=0
XDEBUG

echo "=================="
echo "Configuring Apache"
echo "=================="
rm /etc/httpd/conf.d/welcome.conf
rm -rf /var/www/html
ln -sf /vagrant /var/www/html
service httpd start > /dev/null 2>&1

echo "===================="
echo "Configuring Firewall"
echo "===================="
iptables -I INPUT 5 -p tcp -m state --state=NEW --dport 80 -j ACCEPT
iptables -I INPUT 6 -p tcp -m state --state=NEW --dport 3306 -j ACCEPT
iptables -I INPUT 7 -p tcp -m state --state=NEW --dport 5432 -j ACCEPT

echo "============================"
echo "Establishing Writable Mounts"
echo "============================"
mkdir -p "$CONFIG/core" "$FILES" "$IMAGES" "$LOGS"
chown -R apache:apache "$CONFIG" "$FILES" "$IMAGES" "$LOGS"
mount --bind "$CONFIG" /vagrant/config
mount --bind "$FILES" /vagrant/files
mount --bind "$IMAGES" /vagrant/images
mount --bind "$LOGS" /vagrant/logs

# Helpful Information
cat << USAGE
===============================================================================

 Thanks for trying phpWebSite!

 The server instance is now set up for you, but you will need to go through
 the phpWebSite "setup" process.

 Database Connection Information for this VM:

          Database Type: [either MySQL or PostgreSQL, both work]
          Database Name: phpwebsite
          Database User: phpwebsite
      Database Password: phpwebsite
     Port Specification: [leave blank]
     Host Specification: [leave blank]

 Available Forwarded Ports (connect to localhost:xxxx):

            SSH: 2222
           HTTP: 7970
          MySQL: 7971
     PostgreSQL: 7972

 Please note that remote paths have been mounted on top of config, files,
 images, and logs.  Anything created within Vagrant in these directories will
 not appear locally, and anything created locally will not appear in Vagrant.
 Additionally, all server-side contents of these directories WILL BE LOST after
 you vagrant destroy.

 To connect to MySQL from the command-line client:
     mysql -h localhost -P 7971 --protocol=TCP -u root
 Or, through phpMyAdmin:
     http://localhost:7970/phpMyAdmin
     user: root
     [leave password blank]
 
 To connect to PostgreSQL from the command-line client:
     psql -h localhost -p 7972 -U postgres
 Or, through phpPgAdmin:
     http://localhost:7970/phpPgAdmin
     user: postgres
     [leave password blank]

 And of course, phpWebSite is located at:
     http://localhost:7970
 so head on over and get started!

===============================================================================
USAGE
