<?php

/**
 * Simple class to add a module's administrator commands to a box
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

class MiniAdmin {

    public static function add($module, $links)
    {
        if (is_array($links)) {
            foreach ($links as $link) {
                Controlpanel::getToolbar()->addSiteOption($module, $link);
            }
            return true;
        }

        Controlpanel::getToolbar()->addSiteOption($module, $links);
        return true;
    }
}

?>