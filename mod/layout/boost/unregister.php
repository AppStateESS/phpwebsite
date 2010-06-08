<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

function layout_unregister($module, &$content){

    \core\Core::initModClass('layout', 'Box.php');
    $content[] = dgettext('layout', 'Removing old layout components.');

    $db = new \core\DB('layout_box');
    $db->addWhere('module', $module);
    $moduleBoxes = $db->getObjects('Layout_Box');

    if (empty($moduleBoxes)) {
        return;
    }

    if (core\Error::isError($moduleBoxes)) {
        return $moduleBoxes;
    }

    foreach ($moduleBoxes as $box) {
        $box->kill();
    }

    // below makes sure box doesn't get echoed
    unset($GLOBALS['Layout'][$module]);
    unset($_SESSION['Layout_Settings']->_boxes[$module]);
}

?>
