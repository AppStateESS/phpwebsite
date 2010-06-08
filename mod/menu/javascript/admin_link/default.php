<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

javascript('jquery_ui');

$key = Core\Key::getCurrent();
if (!Core\Key::checkKey($key)) {
    $key = new Core\Key;
}

$data['delete_question'] = dgettext('menu', 'Are you sure you want to delete this link:');
$data['reference_key']   = $key->id;
if (!empty($data['drag_sort'])) {
    $data['show_drag_sort'] = ' ';
}

?>