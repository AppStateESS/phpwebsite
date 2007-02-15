<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

translate('clipboard');

//define('CLIPBOARD_DROP_LINK', _('Drop'));
define('CLIPBOARD_DROP_LINK', 
       sprintf('<img src="./images/mod/clipboard/gtk-delete.png" title="%s"/>', _('Drop')));

translate();
?>