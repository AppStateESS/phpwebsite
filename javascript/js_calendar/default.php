<?php
  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

$default['date_name'] = 'date';
$default['type'] = 'text';

if ( !isset($data['type']) || ( $data['type'] != 'text' && $data['type'] != 'select') ) {
    $data['type'] = &$default['type'];
 }

if ($data['type'] == 'select') {
    $bodyfile = $base . 'javascript/' . $directory . '/body2.js';
 }
?>