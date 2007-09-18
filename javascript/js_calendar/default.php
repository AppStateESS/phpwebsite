<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

if (!isset($data['form_name'])) {
     $data['form_name'] = 'phpws_form';
} else {
     $data['form_name'] = $data['form_name'];
}

$default['date_name'] = 'date';
$default['type'] = 'text';

if ( !isset($data['type']) || 
     ( $data['type'] != 'text' && $data['type'] != 'select' && $data['type'] != 'pick') ) {
    $data['type'] = &$default['type'];
 }

if ($data['type'] == 'select') {
    $bodyfile = $base . 'javascript/js_calendar/body2.js';
} elseif ($data['type'] == 'select_clock') {
    $bodyfile = $base . 'javascript/js_calendar/body4.js';
}

if ($data['type'] == 'pick') {
    if (empty($data['year'])) {
        $data['year'] = date('Y');
    }

    if (empty($data['month'])) {
        $data['month'] = date('m');
    }

    if (empty($data['day'])) {
        $data['day'] = date('d');
    }

    $bodyfile = $base . 'javascript/js_calendar/body3.js';
 }

$data['alt'] = _('Pick date');

?>