<?php

/**
 * Blog init file
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

Core\Core::configRequireOnce('blog', 'config.php');
Core\Core::initModClass('blog', 'Blog_User.php');

?>