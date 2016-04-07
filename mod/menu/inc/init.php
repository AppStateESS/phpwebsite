<?php
/**
 * Initializes the menu class
 *
 * @author Matthew McNaney <mcnaney at gmail dot com
 * @version $Id$
 */

\phpws\PHPWS_Core::requireConfig('menu', 'config.php');
\phpws\PHPWS_Core::initModClass('menu', 'Menu.php');
