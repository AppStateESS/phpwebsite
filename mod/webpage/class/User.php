<?php

/**
 * Controls the general user functionality of the module
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

PHPWS_Core::initModClass('webpage', 'Volume.php');

class Webpage_User {
    function main($command=NULL)
    {
        if (empty($command)) {
            if (isset($_REQUEST['wp_user'])) {
                $command = $_REQUEST['wp_user'];
            } else {
                PHPWS_Core::errorPage(404);
                exit();
            }
        }

        switch ($command) {
        case 'view':
            if (!isset($_REQUEST['id'])) {
                PHPWS_Core::errorPage(404);
                exit();
            }

            $volume = & new Webpage_Volume($_REQUEST['id']);
            $volume->loadKey();
            if (!$volume->_key->allowView()) {
                PHPWS_Core::errorPage(404);
            }
            @$page = $_REQUEST['page'];
            Layout::add($volume->view($page));
            PHPWS_Core::initModClass('menu', 'Menu.php');
            break;

        default:
            echo $command;
            break;
        }

    }

    function showFrontPage()
    {
        if (isset($_REQUEST['module'])) {
            return NULL;
        }

        PHPWS_Core::initModClass('webpage', 'Volume.php');

        $db = & new PHPWS_DB('webpage_volume');
        $db->addWhere('frontpage', 1);
        $db->addWhere('approved', 1);
        Key::restrictView($db, 'webpage');
        $result = $db->getObjects('Webpage_Volume');

        if (PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return NULL;
        }

        if (empty($result)) {
            return NULL;
        }

        foreach ($result as $volume) {
            $volume->loadPages();
            Layout::add($volume->view(), 'webpage', 'page_view', TRUE);
        }
    }
}


?>