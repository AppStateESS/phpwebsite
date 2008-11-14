<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

$key = Key::getCurrent();
if (!Key::checkKey($key)) {
    $key = new Key;
}

$data['delete_question'] = dgettext('menu', 'Are you sure you want to delete this link:');
$data['reference_key']   = $key->id;
if ($data['drag_sort']) {
    $data['show_drag_sort'] = ' ';
}

?>