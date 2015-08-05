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
    if ($db->isTableColumn('deleted')) {
        $db->addWhere('deleted', 0);
    }
    Key::restrictView($db, 'pagesmith');
    $db->loadClass('pagesmith', 'PS_Page.php');
    $result = $db->getObjects('PS_Page');
    if (!PHPWS_Error::logIfError($result) && !empty($result)) {
        PHPWS_Core::initModClass('pagesmith', 'PageSmith.php');
        foreach ($result as $page) {
            $content = $page->view();
            if ($content && !PHPWS_Error::logIfError($content)) {
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