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
} elseif ( isset($_GET['var1']) ) {
    if (isset($_GET['var2'])) {
        if(strtolower($_GET['var2']) == 'image') {
            $cabinet->viewImage($_GET['var1']);
        } elseif (strtolower($_GET['var2']) == 'multimedia') {
            $cabinet->viewMultimedia($_GET['var1']);
        }
    } elseif (isset($_GET['var2']) && strtolower($_GET['var2']) == 'folder') {
        $_REQUEST['uop'] = 'view_folder';
        $_REQUEST['folder_id'] = (int)$_GET['var1'];
        $cabinet->user();
    } else {
        $cabinet->download($_GET['var1']);
    }
}

?>