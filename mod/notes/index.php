<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

Deprecate::moduleWarning('notes');

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../core/conf/404.html';
    exit();
}
if (!isset($_REQUEST['command'])) {
    return;
}

switch ($_REQUEST['command']) {
    case 'close_notes':
        $_SESSION['No_Notes'] = 1;
        PHPWS_Core::goBack();
        break;

    case 'delete_note':
        PHPWS_Core::initModClass('notes', 'Note_Item.php');
        $note = new Note_Item((int)$_REQUEST['id']);
        $result = $note->delete();
        if (PHPWS_Error::isError($result)) {
            PHPWS_Error::log($result);
        }

        Layout::nakedDisplay(javascript('close_refresh'));
        exit();
        break;

    case 'search_users':
        if (!Current_User::isLogged()) {
            exit();
        }
        $db = new PHPWS_DB('users');
        if (empty($_GET['q'])) {
            exit();
        }

        $username = preg_replace('/[^\w\s]/', '', $_GET['q']);
        $db->addWhere('username', $username, 'regexp');
        $db->addWhere('display_name', $username, 'regexp', 'or');
        $db->addColumn('display_name');
        $db->addColumn('id');
        $db->setIndexBy('id');
        $result = $db->select('col');
        if (!empty($result) && !PHPWS_Error::logIfError($result)) {
            foreach ($result as $key=>$value) {
                $output[] = "$value|$key";
            }
            echo implode("\n", $output);
        }
        exit();
        break;
}

?>