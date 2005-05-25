<?php

/**
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

if (!Current_User::authorized('menu')) {
  Current_User::disallow();
}

Menu::admin();

?>