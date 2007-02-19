<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */


if (!class_exists('PHPWS_User')){
    return;
 }

if (!isset($_SESSION['User'])){
    $_SESSION['User'] = new PHPWS_User;
 }
translate('users');
Current_User::getLogin();
translate();

?>
