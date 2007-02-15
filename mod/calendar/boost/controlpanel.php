<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

translate('calendar');
$link[] = array('label'       => _('Calendar'),
		'restricted'  => TRUE,
		'url'         => 'index.php?module=calendar&amp;aop=schedules',
		'description' => _('Create events and schedules.'),
		'image'       => 'calendar.png',
		'tab'         => 'content'
		);
translate();

?>