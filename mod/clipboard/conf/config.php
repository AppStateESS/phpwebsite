<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

//define('CLIPBOARD_DROP_LINK', dgettext('clipboard', 'Drop'));
define('CLIPBOARD_DROP_LINK', 
       sprintf('<img src="./images/mod/clipboard/gtk-delete.png" title="%s"/>',
               dgettext('clipboard', 'Drop')));
?>