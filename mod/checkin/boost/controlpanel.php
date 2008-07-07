<?php

  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at appstate dot edu>
   */

$link[] = array('label'       => 'Checkin',
		'restricted'  => TRUE,
		'url'         => 'index.php?module=checkin&amp;aop=switch',
		'description' => dgettext('Checkin', 'Assigns walk-in visitors to staff members and tracks their time.'),
		'image'       => 'checkin.png',
		'tab'         => 'content'
		);

?>