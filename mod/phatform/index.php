<?php

/**
 * Index file for phatform module
 *
 * @version $Id$
 */

$GLOBALS['CNT_phatform']['title'] = $GLOBALS['CNT_phatform']['content'] = NULL;
$GLOBALS['CNT_phatform']['message'] = NULL;
if(!defined('PHPWS_SOURCE_DIR')) {
    include '../../core/conf/404.html';
    exit();
}

if (isset($_GET['id'])) {
    $_REQUEST['PHAT_MAN_OP'] = 'view';
    $_REQUEST['PHPWS_MAN_ITEMS'][] = $_GET['id'];
}

if (!isset($_SESSION['PHAT_FormManager'])) {
    $_SESSION['PHAT_FormManager'] = new PHAT_FormManager;
}

/* Include the phatform config file */
require_once(PHPWS_SOURCE_DIR . 'mod/phatform/class/advViews.php');


/* Look for the PHAT MAN :) */
if(isset($_REQUEST['PHAT_MAN_OP'])) {
    $_SESSION['PHAT_FormManager']->managerAction();
    $_SESSION['PHAT_FormManager']->action();
}



if(isset($_REQUEST['EXPORT_OP'])) {
    $_SESSION['PHAT_advViews']->exportActions();
} else if(isset($_REQUEST['ARCHIVE_OP'])) {
    $_SESSION['PHAT_advViews']->archiveActions();
}

/* Check for PHAT_Form operation */
if(isset($_REQUEST['PHAT_FORM_OP'])) {
    check_session();
    $_SESSION['PHAT_FormManager']->form->action();
}

/* Where's the PHAT EL? */
if(isset($_REQUEST['PHAT_EL_OP'])) {
    check_session();
    $_SESSION['PHAT_FormManager']->form->element->action();
}

/* Check to see if there is a reprt operation */
if(isset($_REQUEST['PHAT_REPORT_OP'])) {
    check_session();
    $_SESSION['PHAT_FormManager']->form->report->action();
}

function check_session() {
    if(!isset($_SESSION['PHAT_FormManager']->form)) {
        header('Location: ./admin');
        exit();
    }
}

$tpl['TITLE'] = $GLOBALS['CNT_phatform']['title'];
$tpl['MESSAGE'] = $GLOBALS['CNT_phatform']['message'];
$tpl['CONTENT'] = $GLOBALS['CNT_phatform']['content'];

Layout::add(Core\Template::process($tpl, 'phatform', 'box.tpl'));

?>