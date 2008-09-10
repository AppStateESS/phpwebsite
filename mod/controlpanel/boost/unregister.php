<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function controlpanel_unregister($module, &$content)
{
    PHPWS_Core::initModClass('controlpanel', 'ControlPanel.php');
    return PHPWS_ControlPanel::unregisterModule($module, $content);
}
?>
