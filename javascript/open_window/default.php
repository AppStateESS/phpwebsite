<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

$default['label']       = 'No label';
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
$default['center']      = 1;

if (isset($data['type'])) {
    if ($data['type'] == 'button') {
        $bodyfile = PHPWS_SOURCE_DIR . 'javascript/open_window/body2.js';
    }
}

if (isset($data['center'])) {
    $data['center'] = (bool)$data['center'] ? 1 : 0;
}

$site_address = PHPWS_Core::getHomeHttp();

if (!stristr($data['address'], $site_address)) {
    $data['address'] = $site_address .  $data['address'];
}

if (!empty($data['secure'])) {
    if (!isset($_GLOBALS['open_window_reset'])) {
        unset($_SESSION['secure_open_window']);
        $_GLOBALS['open_window_reset'] = true;
    }
    $rand = PHPWS_Text::randomString();
    $data['address'] .= '&amp;owpop=' . $rand;
    $_SESSION['secure_open_window'][] = $rand;
    $data['id'] = $rand;
}

?>