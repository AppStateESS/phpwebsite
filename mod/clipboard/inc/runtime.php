<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

if (!isset($_SESSION['Clipboard'])) {
  return;
}
translate('clipboard');
Clipboard::show();
translate();
?>