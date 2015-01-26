#!/usr/bin/env bash

# This is a work-in-progress provisioning script for Vagrant.
# It's not done yet.  Don't use it.

# Configuration
DBUSER=phpwebsite
DBPASS=phpwebsite
DBNAME=phpwebsite

RC_DBUSER=roundcube
RC_DBPASS=roundcube
RC_DBNAME=roundcube
RC_VERSION=0.9.5

CONFIG=/var/phpws/config
FILES=/var/phpws/files
IMAGES=/var/phpws/images
LOGS=/var/phpws/logs
# Need this because phpws images directory is no longer in the repository so we need to create it in the vagrant box
PHPWS_IMAGES=/vagrant/images

echo "================"
echo "PHPWEBSITE 1.9.x"
echo "================"

echo "==================="
echo "Installing Packages"
echo "==================="
yum -y install http://download.fedoraproject.org/pub/epel/6/x86_64/epel-release-6-8.noarch.rpm
yum -y install httpd php-cli php-pgsql php-pecl-xdebug php-pdo php \
    php-mbstring php-common php-mysql php-soap php-gd php-xml php-pecl-apc \
    mysql-server mysql postgresql-server postgresql phpmyadmin phpPgAdmin \
    roundcubemail dovecot openoffice.org-headless

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
CREATE DATABASE $RC_DBNAME;
GRANT ALL ON $RC_DBNAME.* TO $RC_DBUSER@localhost IDENTIFIED BY '$RC_DBPASS';
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

echo "=================="
echo "Setting up Postfix"
echo "=================="
useradd email -G mail
echo "email" | passwd email --stdin
cat << POSTFIX >> /etc/postfix/main.cf
transport_maps = hash:/etc/postfix/transport
always_bcc = email@localhost
POSTFIX
cat << TRANSPORT >> /etc/postfix/transport
localhost :
*         :discard
TRANSPORT
postmap /etc/postfix/transport
service postfix restart

echo "=================="
echo "Setting up Dovecot"
echo "=================="
cat << DOVECOT > /etc/dovecot/dovecot.conf
mail_location = mbox:~/mail:INBOX=/var/mail/%u
mbox_write_locks = fcntl
passdb {
  driver = pam
  }
  ssl_cert = </etc/pki/dovecot/certs/dovecot.pem
  ssl_key = </etc/pki/dovecot/private/dovecot.pem
  userdb {
    driver = passwd
}
DOVECOT
service dovecot start

echo "===================="
echo "Setting up RoundCube"
echo "===================="
cat << RCDB > /etc/roundcubemail/db.inc.php
<?php
\$rcmail_config = array();
\$rcmail_config['db_dsnw'] = 'mysql://$RC_DBUSER:$RC_DBPASS@localhost/$RC_DBNAME';
\$rcmail_config['db_dsnr'] = '';
\$rcmail_config['db_persistent'] = FALSE;
\$rcmail_config['db_table_users'] = 'users';
\$rcmail_config['db_table_identities'] = 'identities';
\$rcmail_config['db_table_contacts'] = 'contacts';
\$rcmail_config['db_table_contactgroups'] = 'contactgroups';
\$rcmail_config['db_table_contactgroupmembers'] = 'contactgroupmembers';
\$rcmail_config['db_table_session'] = 'session';
\$rcmail_config['db_table_cache'] = 'cache';
\$rcmail_config['db_table_cache_index'] = 'cache_index';
\$rcmail_config['db_table_cache_thread'] = 'cache_thread';
\$rcmail_config['db_table_cache_messages'] = 'cache_messages';
\$rcmail_config['db_table_dictionary'] = 'dictionary';
\$rcmail_config['db_table_searches'] = 'searches';
\$rcmail_config['db_table_system'] = 'system';
\$rcmail_config['db_sequence_users'] = 'user_ids';
\$rcmail_config['db_sequence_identities'] = 'identity_ids';
\$rcmail_config['db_sequence_contacts'] = 'contact_ids';
\$rcmail_config['db_sequence_contactgroups'] = 'contactgroups_ids';
\$rcmail_config['db_sequence_searches'] = 'search_ids';
?>
RCDB
cat << RCMAIN > /etc/roundcubemail/main.inc.php
<?php
\$rcmail_config = array();
\$rcmail_config['debug_level'] = 1;
\$rcmail_config['log_driver'] = 'file';
\$rcmail_config['log_date_format'] = 'd-M-Y H:i:s O';
\$rcmail_config['syslog_id'] = 'roundcube';
\$rcmail_config['syslog_facility'] = LOG_USER;
\$rcmail_config['smtp_log'] = true;
\$rcmail_config['log_logins'] = false;
\$rcmail_config['log_session'] = false;
\$rcmail_config['sql_debug'] = false;
\$rcmail_config['imap_debug'] = false;
\$rcmail_config['ldap_debug'] = false;
\$rcmail_config['smtp_debug'] = false;
\$rcmail_config['default_host'] = 'localhost';
\$rcmail_config['default_port'] = 143;
\$rcmail_config['imap_auth_type'] = null;
\$rcmail_config['imap_delimiter'] = null;
\$rcmail_config['imap_ns_personal'] = null;
\$rcmail_config['imap_ns_other']    = null;
\$rcmail_config['imap_ns_shared']   = null;
\$rcmail_config['imap_force_caps'] = false;
\$rcmail_config['imap_force_lsub'] = false;
\$rcmail_config['imap_force_ns'] = false;
\$rcmail_config['imap_timeout'] = 0;
\$rcmail_config['imap_auth_cid'] = null;
\$rcmail_config['imap_auth_pw'] = null;
\$rcmail_config['imap_cache'] = null;
\$rcmail_config['messages_cache'] = false;
\$rcmail_config['smtp_server'] = '';
\$rcmail_config['smtp_port'] = 25;
\$rcmail_config['smtp_user'] = '';
\$rcmail_config['smtp_pass'] = '';
\$rcmail_config['smtp_auth_type'] = '';
\$rcmail_config['smtp_auth_cid'] = null;
\$rcmail_config['smtp_auth_pw'] = null;
\$rcmail_config['smtp_helo_host'] = '';
\$rcmail_config['smtp_timeout'] = 0;
\$rcmail_config['enable_installer'] = false;
\$rcmail_config['dont_override'] = array();
\$rcmail_config['support_url'] = '';
\$rcmail_config['skin_logo'] = null;
\$rcmail_config['auto_create_user'] = true;
\$rcmail_config['user_aliases'] = false;
\$rcmail_config['log_dir'] = '/var/log/roundcubemail/';
\$rcmail_config['temp_dir'] = '\${_tmppath}';
\$rcmail_config['message_cache_lifetime'] = '10d';
\$rcmail_config['force_https'] = false;
\$rcmail_config['use_https'] = false;
\$rcmail_config['login_autocomplete'] = 0;
\$rcmail_config['login_lc'] = 2;
\$rcmail_config['skin_include_php'] = false;
\$rcmail_config['display_version'] = false;
\$rcmail_config['session_lifetime'] = 10;
\$rcmail_config['session_domain'] = '';
\$rcmail_config['session_name'] = null;
\$rcmail_config['session_auth_name'] = null;
\$rcmail_config['session_path'] = null;
\$rcmail_config['session_storage'] = 'db';
\$rcmail_config['memcache_hosts'] = null;
\$rcmail_config['ip_check'] = false;
\$rcmail_config['referer_check'] = false;
\$rcmail_config['x_frame_options'] = 'sameorigin';
\$rcmail_config['des_key'] = 'AGVuQql0VnRCMUosAmH32WWz';
\$rcmail_config['username_domain'] = '';
\$rcmail_config['mail_domain'] = '';
\$rcmail_config['password_charset'] = 'ISO-8859-1';
\$rcmail_config['sendmail_delay'] = 0;
\$rcmail_config['max_recipients'] = 0; 
\$rcmail_config['max_group_members'] = 0; 
\$rcmail_config['useragent'] = 'Roundcube Webmail/'.RCMAIL_VERSION;
\$rcmail_config['product_name'] = 'Roundcube Webmail';
\$rcmail_config['include_host_config'] = false;
\$rcmail_config['generic_message_footer'] = '';
\$rcmail_config['generic_message_footer_html'] = '';
\$rcmail_config['http_received_header'] = false;
\$rcmail_config['http_received_header_encrypt'] = false;
\$rcmail_config['mail_header_delimiter'] = NULL;
\$rcmail_config['line_length'] = 72;
\$rcmail_config['send_format_flowed'] = true;
\$rcmail_config['mdn_use_from'] = false;
\$rcmail_config['identities_level'] = 0;
\$rcmail_config['client_mimetypes'] = null;
\$rcmail_config['mime_magic'] = null;
\$rcmail_config['mime_types'] = null;
\$rcmail_config['im_identify_path'] = null;
\$rcmail_config['im_convert_path'] = null;
\$rcmail_config['image_thumbnail_size'] = 240;
\$rcmail_config['contact_photo_size'] = 160;
\$rcmail_config['email_dns_check'] = false;
\$rcmail_config['no_save_sent_messages'] = false;
\$rcmail_config['plugins'] = array();
\$rcmail_config['message_sort_col'] = '';
\$rcmail_config['message_sort_order'] = 'DESC';
\$rcmail_config['list_cols'] = array('subject', 'status', 'fromto', 'date', 'size', 'flag', 'attachment');
\$rcmail_config['language'] = null;
\$rcmail_config['date_format'] = 'Y-m-d';
\$rcmail_config['date_formats'] = array('Y-m-d', 'Y/m/d', 'Y.m.d', 'd-m-Y', 'd/m/Y', 'd.m.Y', 'j.n.Y');
\$rcmail_config['time_format'] = 'H:i';
\$rcmail_config['time_formats'] = array('G:i', 'H:i', 'g:i a', 'h:i A');
\$rcmail_config['date_short'] = 'D H:i';
\$rcmail_config['date_long'] = 'Y-m-d H:i';
\$rcmail_config['drafts_mbox'] = 'Drafts';
\$rcmail_config['junk_mbox'] = 'Junk';
\$rcmail_config['sent_mbox'] = 'Sent';
\$rcmail_config['trash_mbox'] = 'Trash';
\$rcmail_config['default_folders'] = array('INBOX', 'Drafts', 'Sent', 'Junk', 'Trash');
\$rcmail_config['create_default_folders'] = false;
\$rcmail_config['protect_default_folders'] = true;
\$rcmail_config['quota_zero_as_unlimited'] = false;
\$rcmail_config['enable_spellcheck'] = true;
\$rcmail_config['spellcheck_dictionary'] = false;
\$rcmail_config['spellcheck_engine'] = 'googie';
\$rcmail_config['spellcheck_uri'] = '';
\$rcmail_config['spellcheck_languages'] = NULL;
\$rcmail_config['spellcheck_ignore_caps'] = false;
\$rcmail_config['spellcheck_ignore_nums'] = false;
\$rcmail_config['spellcheck_ignore_syms'] = false;
\$rcmail_config['recipients_separator'] = ',';
\$rcmail_config['max_pagesize'] = 200;
\$rcmail_config['min_refresh_interval'] = 60;
\$rcmail_config['upload_progress'] = false;
\$rcmail_config['undo_timeout'] = 0;
\$rcmail_config['address_book_type'] = 'sql';
\$rcmail_config['ldap_public'] = array();
\$rcmail_config['autocomplete_addressbooks'] = array('sql');
\$rcmail_config['autocomplete_min_length'] = 1;
\$rcmail_config['autocomplete_threads'] = 0;
\$rcmail_config['autocomplete_max'] = 15;
\$rcmail_config['address_template'] = '{street}<br/>{locality} {zipcode}<br/>{country} {region}';
\$rcmail_config['addressbook_search_mode'] = 0;
\$rcmail_config['default_charset'] = 'ISO-8859-1';
\$rcmail_config['skin'] = 'larry';
\$rcmail_config['mail_pagesize'] = 50;
\$rcmail_config['addressbook_pagesize'] = 50;
\$rcmail_config['addressbook_sort_col'] = 'surname';
\$rcmail_config['addressbook_name_listing'] = 0;
\$rcmail_config['timezone'] = 'auto';
\$rcmail_config['prefer_html'] = true;
\$rcmail_config['show_images'] = 0;
\$rcmail_config['message_extwin'] = false;
\$rcmail_config['compose_extwin'] = false;
\$rcmail_config['htmleditor'] = 0;
\$rcmail_config['prettydate'] = true;
\$rcmail_config['draft_autosave'] = 300;
\$rcmail_config['preview_pane'] = false;
\$rcmail_config['preview_pane_mark_read'] = 0;
\$rcmail_config['logout_purge'] = false;
\$rcmail_config['logout_expunge'] = false;
\$rcmail_config['inline_images'] = true;
\$rcmail_config['mime_param_folding'] = 1;
\$rcmail_config['skip_deleted'] = false;
\$rcmail_config['read_when_deleted'] = true;
\$rcmail_config['flag_for_deletion'] = false;
\$rcmail_config['refresh_interval'] = 60;
\$rcmail_config['check_all_folders'] = false;
\$rcmail_config['display_next'] = true;
\$rcmail_config['autoexpand_threads'] = 0;
\$rcmail_config['reply_mode'] = 0;
\$rcmail_config['strip_existing_sig'] = true;
\$rcmail_config['show_sig'] = 1;
\$rcmail_config['force_7bit'] = false;
\$rcmail_config['search_mods'] = null;
\$rcmail_config['addressbook_search_mods'] = null;
\$rcmail_config['delete_always'] = false;
\$rcmail_config['delete_junk'] = false;
\$rcmail_config['mdn_requests'] = 0;
\$rcmail_config['mdn_default'] = 0;
\$rcmail_config['dsn_default'] = 0;
\$rcmail_config['reply_same_folder'] = false;
\$rcmail_config['forward_attachment'] = false;
\$rcmail_config['default_addressbook'] = null;
\$rcmail_config['spellcheck_before_send'] = false;
\$rcmail_config['autocomplete_single'] = false;
\$rcmail_config['default_font'] = 'Verdana';
RCMAIN
cat << RCHTTPD > /etc/httpd/conf.d/roundcubemail.conf
Alias /roundcubemail /usr/share/roundcubemail
<Directory /usr/share/roundcubemail/>
    <IfModule mod_authz_core.c>
        # Apache 2.4
        Require local
    </IfModule>
    <IfModule !mod_authz_core.c>
        # Apache 2.2
        Order Deny,Allow
        Allow from all
    </IfModule>
</Directory>
RCHTTPD
mysql $RC_DBNAME < /usr/share/doc/roundcubemail-$RC_VERSION/SQL/mysql.initial.sql
useradd email
echo "email" | passwd email --stdin

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
$conf['servers'][0]['host'] = null;
$conf['servers'][0]['port'] = 5432;
$conf['servers'][0]['sslmode'] = 'allow';
$conf['servers'][0]['defaultdb'] = 'phpwebsite';
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

echo "==============="
echo "Configuring PHP"
echo "==============="
rm /etc/php.ini
cat << PHP > /etc/php.ini
[PHP]
engine = On
short_open_tag = On
asp_tags = Off
precision = 14
y2k_compliance = On
output_buffering = 4096
zlib.output_compression = Off
implicit_flush = Off
unserialize_callback_func =
serialize_precision = 100
allow_call_time_pass_reference = Off
safe_mode = Off
safe_mode_gid = Off
safe_mode_include_dir =
safe_mode_exec_dir =
safe_mode_allowed_env_vars = PHP_
safe_mode_protected_env_vars = LD_LIBRARY_PATH
disable_functions =
disable_classes =
expose_php = On
max_execution_time = 30     
max_input_time = 60
memory_limit = 128M
error_reporting = E_ALL & ~E_DEPRECATED
display_errors = On
display_startup_errors = Off
log_errors = On
log_errors_max_len = 1024
ignore_repeated_errors = Off
ignore_repeated_source = Off
report_memleaks = On
track_errors = Off
html_errors = Off
variables_order = "GPCS"
request_order = "GP"
register_globals = Off
register_long_arrays = Off
register_argc_argv = Off
auto_globals_jit = On
post_max_size = 8M
magic_quotes_gpc = Off
magic_quotes_runtime = Off
magic_quotes_sybase = Off
auto_prepend_file =
auto_append_file =
default_mimetype = "text/html"
doc_root =
user_dir =
enable_dl = Off
file_uploads = On
upload_max_filesize = 2M
allow_url_fopen = On
allow_url_include = Off
default_socket_timeout = 60
[Date]
date.timezone = 'America/New_York'
[filter]
[iconv]
[intl]
[sqlite]
[sqlite3]
[Pcre]
[Pdo]
[Phar]
[Syslog]
define_syslog_variables  = Off
[mail function]
SMTP = localhost
smtp_port = 25
sendmail_path = /usr/sbin/sendmail -t -i
mail.add_x_header = On
[SQL]
sql.safe_mode = Off
[ODBC]
odbc.allow_persistent = On
odbc.check_persistent = On
odbc.max_persistent = -1
odbc.max_links = -1
odbc.defaultlrl = 4096
odbc.defaultbinmode = 1
[MySQL]
mysql.allow_persistent = On
mysql.max_persistent = -1
mysql.max_links = -1
mysql.default_port =
mysql.default_socket =
mysql.default_host =
mysql.default_user =
mysql.default_password =
mysql.connect_timeout = 60
mysql.trace_mode = Off
[MySQLi]
mysqli.max_links = -1
mysqli.default_port = 3306
mysqli.default_socket =
mysqli.default_host =
mysqli.default_user =
mysqli.default_pw =
mysqli.reconnect = Off
[PostgresSQL]
pgsql.allow_persistent = On
pgsql.auto_reset_persistent = Off
pgsql.max_persistent = -1
pgsql.max_links = -1
pgsql.ignore_notice = 0
pgsql.log_notice = 0
[Sybase-CT]
sybct.allow_persistent = On
sybct.max_persistent = -1
sybct.max_links = -1
sybct.min_server_severity = 10
sybct.min_client_severity = 10
[bcmath]
bcmath.scale = 0
[browscap]
[Session]
session.save_handler = files
session.save_path = "/var/lib/php/session"
session.use_cookies = 1
session.use_only_cookies = 1
session.name = PHPSESSID
session.auto_start = 0
session.cookie_lifetime = 0
session.cookie_path = /
session.cookie_domain =
session.cookie_httponly = 
session.serialize_handler = php
session.gc_probability = 1
session.gc_divisor = 1000
session.gc_maxlifetime = 1440
session.bug_compat_42 = Off
session.bug_compat_warn = Off
session.referer_check =
session.entropy_length = 0
session.entropy_file =
session.cache_limiter = nocache
session.cache_expire = 180
session.use_trans_sid = 0
session.hash_function = 0
session.hash_bits_per_character = 5
url_rewriter.tags = "a=href,area=href,frame=src,input=src,form=fakeentry"
[MSSQL]
mssql.allow_persistent = On
mssql.max_persistent = -1
mssql.max_links = -1
mssql.min_error_severity = 10
mssql.min_message_severity = 10
mssql.compatability_mode = Off
mssql.secure_connection = Off
[Assertion]
[COM]
[mbstring]
[gd]
[exif]
[Tidy]
tidy.clean_output = Off
[soap]
soap.wsdl_cache_enabled=1
soap.wsdl_cache_dir="/tmp"
soap.wsdl_cache_ttl=86400
[sysvshm]
PHP

echo "=================="
echo "Configuring Apache"
echo "=================="
rm /etc/httpd/conf.d/welcome.conf
cat << HTTPD > /etc/httpd/conf/httpd.conf
ServerTokens OS
ServerRoot "/etc/httpd"
PidFile run/httpd.pid
Timeout 60
KeepAlive Off
MaxKeepAliveRequests 100
KeepAliveTimeout 15
<IfModule prefork.c>
StartServers       8
MinSpareServers    5
MaxSpareServers   20
ServerLimit      256
MaxClients       256
MaxRequestsPerChild  4000
</IfModule>
<IfModule worker.c>
StartServers         4
MaxClients         300
MinSpareThreads     25
MaxSpareThreads     75 
ThreadsPerChild     25
MaxRequestsPerChild  0
</IfModule>
Listen 80
LoadModule auth_basic_module modules/mod_auth_basic.so
LoadModule auth_digest_module modules/mod_auth_digest.so
LoadModule authn_file_module modules/mod_authn_file.so
LoadModule authn_alias_module modules/mod_authn_alias.so
LoadModule authn_anon_module modules/mod_authn_anon.so
LoadModule authn_dbm_module modules/mod_authn_dbm.so
LoadModule authn_default_module modules/mod_authn_default.so
LoadModule authz_host_module modules/mod_authz_host.so
LoadModule authz_user_module modules/mod_authz_user.so
LoadModule authz_owner_module modules/mod_authz_owner.so
LoadModule authz_groupfile_module modules/mod_authz_groupfile.so
LoadModule authz_dbm_module modules/mod_authz_dbm.so
LoadModule authz_default_module modules/mod_authz_default.so
LoadModule ldap_module modules/mod_ldap.so
LoadModule authnz_ldap_module modules/mod_authnz_ldap.so
LoadModule include_module modules/mod_include.so
LoadModule log_config_module modules/mod_log_config.so
LoadModule logio_module modules/mod_logio.so
LoadModule env_module modules/mod_env.so
LoadModule ext_filter_module modules/mod_ext_filter.so
LoadModule mime_magic_module modules/mod_mime_magic.so
LoadModule expires_module modules/mod_expires.so
LoadModule deflate_module modules/mod_deflate.so
LoadModule headers_module modules/mod_headers.so
LoadModule usertrack_module modules/mod_usertrack.so
LoadModule setenvif_module modules/mod_setenvif.so
LoadModule mime_module modules/mod_mime.so
LoadModule dav_module modules/mod_dav.so
LoadModule status_module modules/mod_status.so
LoadModule autoindex_module modules/mod_autoindex.so
LoadModule info_module modules/mod_info.so
LoadModule dav_fs_module modules/mod_dav_fs.so
LoadModule vhost_alias_module modules/mod_vhost_alias.so
LoadModule negotiation_module modules/mod_negotiation.so
LoadModule dir_module modules/mod_dir.so
LoadModule actions_module modules/mod_actions.so
LoadModule speling_module modules/mod_speling.so
LoadModule userdir_module modules/mod_userdir.so
LoadModule alias_module modules/mod_alias.so
LoadModule substitute_module modules/mod_substitute.so
LoadModule rewrite_module modules/mod_rewrite.so
LoadModule proxy_module modules/mod_proxy.so
LoadModule proxy_balancer_module modules/mod_proxy_balancer.so
LoadModule proxy_ftp_module modules/mod_proxy_ftp.so
LoadModule proxy_http_module modules/mod_proxy_http.so
LoadModule proxy_ajp_module modules/mod_proxy_ajp.so
LoadModule proxy_connect_module modules/mod_proxy_connect.so
LoadModule cache_module modules/mod_cache.so
LoadModule suexec_module modules/mod_suexec.so
LoadModule disk_cache_module modules/mod_disk_cache.so
LoadModule cgi_module modules/mod_cgi.so
LoadModule version_module modules/mod_version.so
Include conf.d/*.conf
User apache
Group apache
ServerAdmin root@localhost
UseCanonicalName Off
DocumentRoot "/vagrant"
EnableSendfile off
<Directory />
    Options FollowSymLinks
    AllowOverride None
</Directory>
<Directory "/vagrant">
    Options Indexes FollowSymLinks
    AllowOverride All
    RewriteEngine on
    Order allow,deny
    Allow from all
</Directory>
<IfModule mod_userdir.c>
    #
    # UserDir is disabled by default since it can confirm the presence
    # of a username on the system (depending on home directory
    # permissions).
    #
    UserDir disabled
    #
    # To enable requests to /~user/ to serve the user's public_html
    # directory, remove the "UserDir disabled" line above, and uncomment
    # the following line instead:
    # 
    #UserDir public_html
</IfModule>
DirectoryIndex index.html index.html.var
AccessFileName .htaccess
<Files ~ "^\.ht">
    Order allow,deny
    Deny from all
    Satisfy All
</Files>
TypesConfig /etc/mime.types
DefaultType text/plain
<IfModule mod_mime_magic.c>
    MIMEMagicFile conf/magic
</IfModule>
HostnameLookups Off
ErrorLog logs/error_log
LogLevel warn
LogFormat "%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"" combined
LogFormat "%h %l %u %t \"%r\" %>s %b" common
LogFormat "%{Referer}i -> %U" referer
LogFormat "%{User-agent}i" agent
CustomLog logs/access_log combined
ServerSignature On
Alias /icons/ "/var/www/icons/"
<Directory "/var/www/icons">
    Options Indexes MultiViews FollowSymLinks
    AllowOverride None
    Order allow,deny
    Allow from all
</Directory>
<IfModule mod_dav_fs.c>
    # Location of the WebDAV lock database.
    DAVLockDB /var/lib/dav/lockdb
</IfModule>
ScriptAlias /cgi-bin/ "/var/www/cgi-bin/"
<Directory "/var/www/cgi-bin">
    AllowOverride None
    Options None
    Order allow,deny
    Allow from all
</Directory>
IndexOptions FancyIndexing VersionSort NameWidth=* HTMLTable Charset=UTF-8
AddIconByEncoding (CMP,/icons/compressed.gif) x-compress x-gzip
AddIconByType (TXT,/icons/text.gif) text/*
AddIconByType (IMG,/icons/image2.gif) image/*
AddIconByType (SND,/icons/sound2.gif) audio/*
AddIconByType (VID,/icons/movie.gif) video/*
AddIcon /icons/binary.gif .bin .exe
AddIcon /icons/binhex.gif .hqx
AddIcon /icons/tar.gif .tar
AddIcon /icons/world2.gif .wrl .wrl.gz .vrml .vrm .iv
AddIcon /icons/compressed.gif .Z .z .tgz .gz .zip
AddIcon /icons/a.gif .ps .ai .eps
AddIcon /icons/layout.gif .html .shtml .htm .pdf
AddIcon /icons/text.gif .txt
AddIcon /icons/c.gif .c
AddIcon /icons/p.gif .pl .py
AddIcon /icons/f.gif .for
AddIcon /icons/dvi.gif .dvi
AddIcon /icons/uuencoded.gif .uu
AddIcon /icons/script.gif .conf .sh .shar .csh .ksh .tcl
AddIcon /icons/tex.gif .tex
AddIcon /icons/bomb.gif core
AddIcon /icons/back.gif ..
AddIcon /icons/hand.right.gif README
AddIcon /icons/folder.gif ^^DIRECTORY^^
AddIcon /icons/blank.gif ^^BLANKICON^^
DefaultIcon /icons/unknown.gif
ReadmeName README.html
HeaderName HEADER.html
IndexIgnore .??* *~ *# HEADER* README* RCS CVS *,v *,t
AddLanguage ca .ca
AddLanguage cs .cz .cs
AddLanguage da .dk
AddLanguage de .de
AddLanguage el .el
AddLanguage en .en
AddLanguage eo .eo
AddLanguage es .es
AddLanguage et .et
AddLanguage fr .fr
AddLanguage he .he
AddLanguage hr .hr
AddLanguage it .it
AddLanguage ja .ja
AddLanguage ko .ko
AddLanguage ltz .ltz
AddLanguage nl .nl
AddLanguage nn .nn
AddLanguage no .no
AddLanguage pl .po
AddLanguage pt .pt
AddLanguage pt-BR .pt-br
AddLanguage ru .ru
AddLanguage sv .sv
AddLanguage zh-CN .zh-cn
AddLanguage zh-TW .zh-tw
LanguagePriority en ca cs da de el eo es et fr he hr it ja ko ltz nl nn no pl pt pt-BR ru sv zh-CN zh-TW
ForceLanguagePriority Prefer Fallback
AddDefaultCharset UTF-8
AddType application/x-compress .Z
AddType application/x-gzip .gz .tgz
AddType application/x-x509-ca-cert .crt
AddType application/x-pkcs7-crl    .crl
AddHandler type-map var
AddType text/html .shtml
AddOutputFilter INCLUDES .shtml
Alias /error/ "/var/www/error/"
<IfModule mod_negotiation.c>
<IfModule mod_include.c>
    <Directory "/var/www/error">
        AllowOverride None
        Options IncludesNoExec
        AddOutputFilter Includes html
        AddHandler type-map var
        Order allow,deny
        Allow from all
        LanguagePriority en es de fr
        ForceLanguagePriority Prefer Fallback
    </Directory>
</IfModule>
</IfModule>
BrowserMatch "Mozilla/2" nokeepalive
BrowserMatch "MSIE 4\.0b2;" nokeepalive downgrade-1.0 force-response-1.0
BrowserMatch "RealPlayer 4\.0" force-response-1.0
BrowserMatch "Java/1\.0" force-response-1.0
BrowserMatch "JDK/1\.0" force-response-1.0
BrowserMatch "Microsoft Data Access Internet Publishing Provider" redirect-carefully
BrowserMatch "MS FrontPage" redirect-carefully
BrowserMatch "^WebDrive" redirect-carefully
BrowserMatch "^WebDAVFS/1.[0123]" redirect-carefully
BrowserMatch "^gnome-vfs/1.0" redirect-carefully
BrowserMatch "^XML Spy" redirect-carefully
BrowserMatch "^Dreamweaver-WebDAV-SCM1" redirect-carefully

HTTPD
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
mkdir -p "$CONFIG/core" "$FILES" "$IMAGES" "$PHPWS_IMAGES" "$LOGS"
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

 Regarding Email: This VM is explicitly configured NOT to allow any outbound
 emails.  Instead, they are forwarded verbatim to email@localhost.  You can
 check this account by logging into RoundCube at:
     http://localhost:7970/roundcubemail
     User: email
     Pass: email

 And of course, phpWebSite is located at:
     http://localhost:7970
 so head on over and get started!

===============================================================================
USAGE
