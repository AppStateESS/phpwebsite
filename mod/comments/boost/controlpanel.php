<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

$link[] = array('label'       => dgettext('comments', 'Comments'),
		'restricted'  => TRUE,
		'url'         => 'index.php?module=comments&admin_action=admin_menu',
		'description' => dgettext('comments', 'Control administrative options for comments.'),
		'image'       => 'comments.png',
		'tab'         => 'admin'
		);


?>