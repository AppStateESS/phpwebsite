<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
$data['source_http'] = PHPWS_SOURCE_HTTP;
if (!isset($data['form_name'])) {
    $data['form_name'] = 'phpws_form';
} else {
    $data['form_name'] = $data['form_name'];
}

$default['date_name'] = 'date';
if (isset($data['type'])) {
    switch ($data['type']) {
    case 'select':
        $bodyfile = PHPWS_SOURCE_DIR . 'javascript/js_calendar/body2.js';
        break;

    case 'select_clock':
        $bodyfile = PHPWS_SOURCE_DIR . 'javascript/js_calendar/body4.js';
        break;

    case 'text_clock':
        $bodyfile = PHPWS_SOURCE_DIR . 'javascript/js_calendar/body5.js';
        break;

    case 'pick':
        if (empty($data['year'])) {
            $data['year'] = date('Y');
        }

        if (empty($data['month'])) {
            $data['month'] = date('m');
        }

        if (empty($data['day'])) {
            $data['day'] = date('d');
        }

        $bodyfile = PHPWS_SOURCE_DIR . 'javascript/js_calendar/body3.js';
        break;

    default:
        //body.js used for normal text type
    }
}

$data['alt'] = _('Pick date');

?>