<?php

  /**
   * @version $Id: controlpanel.php 5472 2007-12-11 16:13:40Z jtickle $
   * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
   */

$link[] = array('label'       => 'Analytics',
		'restricted'  => TRUE,
		'url'         => 'index.php?module=analytics&action=ShowAdminSettings',
		'description' => dgettext('analytics', 'Integrate web tracking software into your website.'),
		'image'       => 'analytics.png',
		'tab'         => 'admin'
		);

?>
