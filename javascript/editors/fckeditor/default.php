<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

  // The below depends on Text.php's makeRelative function substituting the home http for "./"
$data['VALUE'] = str_replace('./images/', PHPWS_Core::getHomeHttp() . 'images/', $data['VALUE']);

if (empty($data['WIDTH']) || empty($data['HEIGHT'])) {
    $data['WIDTH'] = 500;
    $data['HEIGHT'] = 250;
 }

if ($data['LIMITED']) {
    $data['config'] = 'limited.js';
 } else {
    $data['config'] = 'custom.js';
 }

if (isset($_REQUEST['module'])) {
    $data['module'] = preg_replace('/\W/', '', $_REQUEST['module']);
}

?>