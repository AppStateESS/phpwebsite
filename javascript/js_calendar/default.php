<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

$default['date_name'] = 'date';
$default['type'] = 'text';

if ( !isset($data['type']) || 
     ( $data['type'] != 'text' && $data['type'] != 'select' && $data['type'] != 'pick') ) {
    $data['type'] = &$default['type'];
 }

if ($data['type'] == 'select') {
    $bodyfile = $base . 'javascript/js_calendar/body2.js';
 }

if ($data['type'] == 'pick') {
    if (empty($data['year'])) {
        $data['year'] = date('Y');
    }

    if (empty($data['month'])) {
        $data['year'] = date('m');
    }

    if (empty($data['day'])) {
        $data['year'] = date('d');
    }

    $bodyfile = $base . 'javascript/js_calendar/body3.js';
 }

?>