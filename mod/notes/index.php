<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */
if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}
if (!isset($_REQUEST['command'])) {
  return;
}

switch ($_REQUEST['command']) {
 case 'close_notes':
   $_SESSION['No_Notes'] = 1;
   PHPWS_Core::goBack();
   break;

 case 'search_users':
     if (!Current_User::isLogged()) {
         exit();
     }
     $db = new PHPWS_DB('users');
     if (empty($_GET['q'])) {
         exit();
     }

     $username = preg_replace('/[^' . ALLOWED_USERNAME_CHARACTERS . ']/', '', $_GET['q']);
     $db->addWhere('username', "$username%", 'like');
     $db->addColumn('username');
     $result = $db->select('col');
     if (!empty($result) && !PHPWS_Error::logIfError($result)) {
         echo implode("\n", $result);         
     }
     exit();
     break;
}

?>