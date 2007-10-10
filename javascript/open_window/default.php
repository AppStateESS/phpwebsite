<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

$default['toolbar']     = 'no';
$default['menubar']     = 'no';
$default['location']    = 'no';
$default['scrollbars']  = 'yes';
$default['resizable']   = 'yes';
$default['width']       = '400';
$default['height']      = '300';
$default['titlebar']    = 'no';
$default['link_title']  = '';
$default['window_name'] = 'default' . rand();
$default['class']       = 'js-open-window';

if (isset($data['type'])) {
    if ($data['type'] = 'button') {
        $bodyfile = $base . 'javascript/open_window/body2.js';
    }
 }

?>