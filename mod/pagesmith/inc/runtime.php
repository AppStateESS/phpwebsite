<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

if (PHPWS_Core::atHome()) {
    showFP();
}


function showFP()
{
    $db = new PHPWS_DB('ps_page');
    $db->addWhere('front_page', 1);
    Key::restrictView($db, 'pagesmith');
    $result = $db->getObjects('PS_Page', 'pagesmith', 'PS_Page.php');
    if (!PHPWS_Error::logIfError($result) && !empty($result)) {
        PHPWS_Core::initModClass('pagesmith', 'PageSmith.php');
        foreach ($result as $page) {
            $content = $page->view();
            if ($content && !PHPWS_Error::logIfError($content)) {
                Layout::add($content, 'pagesmith', 'view_' . $page->id, TRUE);
            }
        }

    } else {
        return null;
    }
}

?>