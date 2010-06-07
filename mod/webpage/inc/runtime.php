<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

if (!isset($_REQUEST['module']) || $_REQUEST['module'] == 'webpage') {
    Core\Core::initModClass('webpage', 'User.php');
    Webpage_User::showFrontPage();
    Webpage_User::showFeatured();
}

?>