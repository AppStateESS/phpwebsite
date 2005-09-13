<?php
/**
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}

if (isset($_REQUEST['command']) && !Current_User::allow('menu')) {
  Current_User::disallow();
}

Menu::admin();

?>