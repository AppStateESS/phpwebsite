<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

if (isset($data['match']) && is_array($data['match'])) {

    foreach ($data['match'] as $match) {
        $link = sprintf('<a href="#" onclick="remove(\'%s\', \'%s\'); return false;">%s</a>',
                        $data['id'], $match, $data['options'][$match]);
        $input = sprintf('<input id="%s-hidden" type="hidden" name="list_item[]" value="%s" />', $data['id'], $match);
        $divs[] = sprintf('<div id="%s-add-%s">%s%s</div>', $data['id'], $match, $link, $input);
        unset($data['options'][$match]);
    }

    $data['default_matches'] = implode("\n", $divs);
}


if (empty($data['options']) || !is_array($data['options'])) {
    $data['option-list'][] = array('value'=>0, 'pick'=> _('No options passed to function'));
} else {
    foreach ($data['options'] as $key=>$opt) {
        $data['option-list'][] = array('value'=>$key, 'pick'=>$opt);
    }
}

$default['add'] = '+';


?>