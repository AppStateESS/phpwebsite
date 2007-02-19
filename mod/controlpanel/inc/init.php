<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

PHPWS_Core::initModClass('controlpanel', 'ControlPanel.php');

if (isset($_REQUEST['module']) && $_REQUEST['module'] == 'controlpanel') {
    PHPWS_Core::initModClass('controlpanel', 'Tab.php');
    PHPWS_Core::initModClass('controlpanel', 'Link.php');
 }

?>