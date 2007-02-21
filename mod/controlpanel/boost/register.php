<?php
/**
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$ 
 */

function controlpanel_register($module, &$content)
{
    PHPWS_Core::initModClass('controlpanel', 'ControlPanel.php');
    translate('controlpanel');
    $result = PHPWS_ControlPanel::registerModule($module, $content);
    translate();
    return $result;
}

?>