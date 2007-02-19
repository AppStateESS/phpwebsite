<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}
translate('rss');
if ( ( isset($_REQUEST['command']) || isset($_REQUEST['tab']) ) && Current_User::allow('rss')) {
    PHPWS_Core::initModClass('rss', 'Admin.php');
    RSS_Admin::main();
 } elseif (isset($_REQUEST['mod_title'])) {
     PHPWS_Core::initModClass('rss', 'RSS.php');
     RSS::viewChannel($_REQUEST['mod_title']);
 } elseif (isset($_REQUEST['id'])) {
     PHPWS_Core::initModClass('rss', 'RSS.php');
     RSS::viewChannel($_REQUEST['id']);
 } else {
    PHPWS_Core::errorPage('404');
 }
translate();
?>