<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

$link[] = array('label'       => dgettext('comments', 'Comments'),
		'restricted'  => TRUE,
		'url'         => 'index.php?module=comments&aop=settings',
		'description' => dgettext('comments', 'Control administrative options for comments.'),
		'image'       => 'comments.png',
		'tab'         => 'admin'
		);


?>