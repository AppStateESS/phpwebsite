<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id
   */

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../core/conf/404.html';
    exit();
}

PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');
$cabinet = new Cabinet;

if (isset($_REQUEST['uop'])) {
    $cabinet->user();
} elseif (isset($_REQUEST['fop'])) {
    $cabinet->fmAdmin();
} elseif (isset($_REQUEST['dop'])) {
    $cabinet->dmAdmin();
} elseif (isset($_REQUEST['iop'])) {
    $cabinet->imAdmin();
} elseif (isset($_REQUEST['mop'])) {
    $cabinet->mmAdmin();
} elseif (isset($_REQUEST['aop']) || isset($_REQUEST['tab'])) {
    $cabinet->admin();
} elseif ( isset($_GET['id']) ) {
    if (isset($_GET['mtype'])) {
        if(strtolower($_GET['mtype']) == 'image') {
            $cabinet->viewImage($_GET['id']);
        } elseif (strtolower($_GET['mtype']) == 'multimedia') {
            $cabinet->viewMultimedia($_GET['id']);
        }
    } elseif (isset($_GET['mtype']) && strtolower($_GET['mtype']) == 'folder') {
        $_REQUEST['uop'] = 'view_folder';
        $_REQUEST['folder_id'] = (int)$_GET['id'];
        $cabinet->user();
    } else {
        $cabinet->download($_GET['id']);
    }
}

?>