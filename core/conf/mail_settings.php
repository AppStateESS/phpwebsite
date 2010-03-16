<?php

/**
 * Config file for PHPWS_Mail class. Based on Pear's Mail class
 *
 * http://pear.php.net/manual/en/package.mail.mail.intro.php
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

/**
 * mail        : php's mail function
 * sendmail    : sendmail program
 * smtp        : Sends mail directly to a smtp server
 */

define('MAIL_BACKEND', 'mail');

/**
 * If using sendmail, path MAY need to be set. Pear's default
 * directory is "/usr/bin/sendmail". If sendmail is in a different
 * directory, uncomment the below and set the correct directory.
 */

//define('SENDMAIL_PATH', '/usr/bin/sendmail');


/**
 * If using smtp, you need to set the settings below. Pear's defaults
 * are as follows:
 * SMTP_HOST    : localhost    : server to connect to
 * SMTP_PORT    : 25           : connection port
 * SMTP_AUTH    : FALSE        : Use SMTP authentication
 * SMTP_USER    : <no default> : Only needed if SMTP_AUTH is TRUE
 * SMTP_PASS    : <no default> : Only needed if SMTP_AUTH is TRUE
 * SMTP_PERSIST : ????         : Indicates if SMTP connection persists on multiple
 *                               send calls
 */

define('SMTP_HOST',    'localhost');
define('SMTP_PORT',    25);
define('SMTP_AUTH',    FALSE);
define('SMTP_USER',    'username');
define('SMTP_PASS',    'password');
define('SMTP_PERSIST', TRUE);

?>