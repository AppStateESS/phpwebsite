<?php

/**
 * Blog init file
 * 
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

PHPWS_Core::configRequireOnce('blog', 'config.php');
PHPWS_Core::initModClass('blog', 'Blog_User.php');

?>