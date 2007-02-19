<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

if (!defined('PHPWS_SOURCE_DIR')) {
    include '../../config/core/404.html';
    exit();
}

translate('clipboard');
if (!isset($_SESSION['Clipboard'])) {
  Clipboard::init();
}

Clipboard::action();
translate();
?>