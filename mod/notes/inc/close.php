<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

if (Current_User::isLogged()) {
    if (!isset($_SESSION['Notes_Show'])) {
        $_SESSION['Notes_Show'] = 1;
    }

    if ($_SESSION['Notes_Show']) {
        PHPWS_Core::initModClass('notes', 'My_Page.php');
        Notes_My_Page::showUnread();
    }
 }

?>