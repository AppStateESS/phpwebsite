<?php

  /**
   * @version $Id: controlpanel.php 4583 2007-04-04 19:12:02Z matt $
   * @author Matthew McNaney <mcnaney at appstate dot edu>
   */

$link[] = array('label'       => 'Alert!',
		'restricted'  => true,
		'url'         => 'index.php?module=alert&amp;aop=main',
		'description' => dgettext('blog', 'Alert your community to important happenings.'),
		'image'       => 'alert.png',
		'tab'         => 'content'
		);
?>