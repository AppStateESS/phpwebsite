<?php

  /**
   * @version $Id$
   * @author  Steven Levin
   * @modified Matthew McNaney <mcnaney at gmail dot com>
   */

if(!defined('PHPWS_SOURCE_DIR')) {
    exit();
 }

$CNT_photoalbum['content'] = NULL;

PHPWS_Core::requireConfig('photoalbum');


if (!isset($_SESSION['PHPWS_AlbumManager']) && (isset($_REQUEST['module']) && $_REQUEST['module'] == 'photoalbum')) {
    $_SESSION['PHPWS_AlbumManager'] = new PHPWS_AlbumManager;
}

if (!empty($_GET['orderby'])) {
    if ($_GET['orderby_dir'] == 'asc') {
        $_SESSION['PHPWS_AlbumManager']->album->order = 1;
    } else {
        $_SESSION['PHPWS_AlbumManager']->album->order = 0;
    }
    $_SESSION['PHPWS_AlbumManager']->album->_orderIds();
}
 

$_SESSION['PHPWS_AlbumManager']->action();

if(isset($_SESSION['PHPWS_AlbumManager']->album)) {
    $_SESSION['PHPWS_AlbumManager']->album->action();
 } 

if(isset($_SESSION['PHPWS_AlbumManager']->album->photo)) {
    $_SESSION['PHPWS_AlbumManager']->album->photo->action();
 }

if(isset($_REQUEST['module']) && ($_REQUEST['module'] != 'photoalbum')) {
    $_SESSION['PHPWS_AlbumManager'] = NULL;
    unset($_SESSION['PHPWS_AlbumManager']);
 }

?>