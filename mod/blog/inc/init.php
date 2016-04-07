<?php

/**
 * Blog init file
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

\phpws\PHPWS_Core::configRequireOnce('blog', 'config.php');
\phpws\PHPWS_Core::initModClass('blog', 'Blog_User.php');
