<?php

/**
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */


if (isset($_REQUEST['command']) && !Current_User::allow('menu')) {
  Current_User::disallow();
}

Menu::admin();

?>