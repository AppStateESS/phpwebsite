<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

if (!defined('PHPWS_SOURCE_DIR')) {
    exit();
}
translate('menu');
Menu::show();
Menu::showPinned();
translate();

?>