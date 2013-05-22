<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

$default['class'] = 'js-slider';
$default['id'] = 'span-' . time();
$default['speed'] = 'fast';

$speed = !empty($data['speed']) ? $data['speed'] : 1;
switch ($speed) {
 case 'fast':
 case 'normal':
 case 'slow':
     break;

 default:
     $data['speed'] = 'fast';
     break;
}

?>