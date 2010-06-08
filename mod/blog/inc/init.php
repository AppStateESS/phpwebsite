<?php

/**
 * Blog init file
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

core\Core::configRequireOnce('blog', 'config.php');
core\Core::initModClass('blog', 'Blog_User.php');

?>