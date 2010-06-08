<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

function filecabinet_unregister($module, &$content)
{
    $db = new \core\DB('folders');
    $db->addValue('module_created', null);
    $db->addWhere('module_created', $module);
    \core\Error::logIfError($db->update());
    $content[] = dgettext('filecabinet', 'Unregistered from File Cabinet.');
    return true;
}

?>