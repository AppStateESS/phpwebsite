<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */


PHPWS_Core::initModClass('search', 'User.php');
translate('search');
Search_User::searchBox();

if (isset($_SESSION['Search_Admin'])) {
    PHPWS_Core::initModClass('search', 'Admin.php');
    Search_Admin::miniAdmin();
 }
translate();

?>