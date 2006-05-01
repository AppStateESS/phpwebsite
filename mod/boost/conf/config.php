<?php


  // if TRUE then only deities can access boost
define('DEITY_ACCESS_ONLY', FALSE);

  // If TRUE, then deities can uninstall core modules
  // Be careful with this, you can kill your site
define('DEITIES_CAN_UNINSTALL', FALSE);

/********** Error Codes ***************/
define('BOOST_ERR_NOT_MODULE',       -1);
define('BOOST_ERR_NO_INSTALLSQL',    -2);
define('BOOST_NO_MODULES_SET',       -3);
define('BOOST_FAILED_PRE94_INSTALL', -4);
define('BOOST_FAILED_PRE94_UPGRADE', -5);
define('BOOST_NO_REGISTER_FILE',     -6);
define('BOOST_NO_REGISTER_FUNCTION', -7);
define('BOOST_FAILED_BACKUP',        -8);
define('BOOST_FAILED_LOCAL_COPY',    -9);

?>