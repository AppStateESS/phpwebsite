<?php
  /**
   * tinymce doesn't respond well to height and width settings.
   * An approximation is made for rows and columns instead.
   *
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

$rows = 20;
$cols = 70;

if (!empty($data['WIDTH'])) {
    $cols = floor($data['WIDTH'] / 6);
 }

if (!empty($data['HEIGHT'])) {
    $rows = floor($data['HEIGHT'] / 21);
 }

$data['rows'] = $rows;
$data['cols'] = $cols;

?>