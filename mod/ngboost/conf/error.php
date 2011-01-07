<?php
/**
 * @version $Id: error.php 7326 2010-03-15 19:38:52Z matt $
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */


$errors[BOOST_ERR_NOT_MODULE]       = dgettext('ngboost', 'Object sent to boost was not of the PHPWS_Module class.');
$errors[BOOST_ERR_NO_INSTALLSQL]    = dgettext('ngboost', 'Unable to locate SQL import file.');
$errors[BOOST_NO_MODULES_SET]       = dgettext('ngboost', 'Modules have not been set.');
$errors[BOOST_FAILED_PRE94_INSTALL] = dgettext('ngboost', 'Failed installation of module.');
$errors[BOOST_FAILED_PRE94_UPGRADE] = dgettext('ngboost', 'Failed upgrade of module.');
$errors[BOOST_NO_REGISTER_FILE]     = dgettext('ngboost', 'Module is missing register file.');
$errors[BOOST_NO_REGISTER_FUNCTION] = dgettext('ngboost', 'Module is missing register function.');
$errors[BOOST_FAILED_BACKUP]        = dgettext('ngboost', 'Unable to make a backup of a local module file.');
$errors[BOOST_FAILED_LOCAL_COPY]    = dgettext('ngboost', 'Failed to copy file to local directory.');

?>