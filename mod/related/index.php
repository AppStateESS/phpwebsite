<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */


if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../core/conf/404.html';
    exit();
}

if (!defined('PHPWS_SOURCE_DIR') || !isset($_REQUEST['action'])) {
    return NULL;
}

PHPWS_CORE::initModClass('related', 'Related.php');
PHPWS_CORE::initModClass('related', 'Action.php');

switch ($_REQUEST['action']) {
    case 'start':
        Related_Action::start();
        break;

    case 'edit':
        $related = new Related($_REQUEST['id']);
        $related->loadFriends();
        $related->setBanked(TRUE);
        Related_Action::newBank($related);
        PHPWS_Core::reroute($related->getUrl());
        break;

    case 'add':
        Related_Action::add();
        break;

    case 'quit':
        Related_Action::quit();
        break;

    case 'up':
        Related_Action::up();
        break;

    case 'down':
        Related_Action::down();
        break;

    case 'remove':
        Related_Action::remove();
        break;
         
    case 'save':
        Related_Action::save();
        break;

    case 'changeForm':
        Related_Action::changeForm();
        break;

    case 'postTitle':
        Related_Action::postTitle();
        break;
}

?>