<?php

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
javascript('jquery');

$style = '<link rel="stylesheet" type="text/css" href="' . PHPWS_SOURCE_HTTP . 'javascript/datetimepicker/jquery.datetimepicker.css" / >';
\Layout::addJSHeader($style);
\Layout::addJSHeader('<script src="' . PHPWS_SOURCE_HTTP . 'javascript/datetimepicker/jquery.datetimepicker.js"></script>');

$default['format'] = null;

$options = array();

if (!empty($data['format'])) {
    $format = $data['format'];
    $options[] = "format:'$format'";
}

if (isset($data['timepicker'])) {
    $options[] = 'timepicker: ' . ($data['timepicker'] ? 'true' : 'false');
}
if (isset($data['datepicker'])) {
    $options[] = 'datepicker: ' . ($data['datepicker'] ? 'true' : 'false');
}
if (isset($data['format'])) {
    $options[] = "format: '" . $data['format'] . "'";
}
if (!isset($data['selector'])) {
    $data['selector'] = '.datetimepicker';
}
$data['options'] = implode(',', $options);
?>
