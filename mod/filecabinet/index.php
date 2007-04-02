<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id
   */

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}

PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
translate('filecabinet');
$cabinet = new Cabinet;
if (isset($_REQUEST['uop'])) {
    $cabinet->user();
} elseif (isset($_REQUEST['aop']) || isset($_REQUEST['tab'])) {
    $cabinet->admin();
} elseif ( isset($_GET['id']) ) {
    if (isset($_GET['page']) && strtolower($_GET['page']) == 'image') {
        $cabinet->viewImage($_GET['id']);
    } elseif (isset($_GET['page']) && strtolower($_GET['page']) == 'folder') {
        $_REQUEST['uop'] = 'view_folder';
        $_REQUEST['folder_id'] = (int)$_GET['id'];
        $cabinet->user();
    } else {
        $cabinet->download($_GET['id']);
    }
}

translate();

?>