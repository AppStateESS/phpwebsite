<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

javascript('jquery_ui');

$key = \core\Key::getCurrent();
if (!core\Key::checkKey($key)) {
    $key = new \core\Key;
}

$data['delete_question'] = dgettext('menu', 'Are you sure you want to delete this link:');
$data['reference_key']   = $key->id;
if (!empty($data['drag_sort'])) {
    $data['show_drag_sort'] = ' ';
}

?>