<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

if (isset($data['onload'])) {
    $onload = (bool)$data['onload'];
 } else {
    $onload = false;
 }

if ($onload) {
    $data['trigger_onload'] = ' ';
}

?>
