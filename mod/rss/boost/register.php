<?php
/**
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

function rss_register($module, &$content)
{
    PHPWS_Core::initModClass('rss', 'RSS.php');
    return RSS::registerModule($module, $content);
}

?>