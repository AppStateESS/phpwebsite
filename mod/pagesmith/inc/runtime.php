<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

if (core\Core::atHome()) {
    showFP();
}


function showFP()
{
    $db = new \core\DB('ps_page');
    $db->addWhere('front_page', 1);
    \core\Key::restrictView($db, 'pagesmith');
    $db->loadClass('pagesmith', 'PS_Page.php');
    $result = $db->getObjects('PS_Page');
    if (!core\Error::logIfError($result) && !empty($result)) {
        \core\Core::initModClass('pagesmith', 'PageSmith.php');
        foreach ($result as $page) {
            $content = $page->view();
            if ($content && !core\Error::logIfError($content)) {
                if (Current_User::allow('pagesmith', 'edit_page', $page->id)) {
                    $content .= sprintf('<p class="pagesmith-edit">%s</p>', $page->editLink());
                }
                Layout::add($content, 'pagesmith', 'view_' . $page->id, TRUE);
            }
        }

    } else {
        return null;
    }
}

?>