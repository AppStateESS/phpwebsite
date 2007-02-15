<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */
translate('comments');
$link[] = array('label'       => _('Comments'),
		'restricted'  => TRUE,
		'url'         => 'index.php?module=comments&admin_action=admin_menu',
		'description' => _('Control administrative options for comments.'),
		'image'       => 'comments.png',
		'tab'         => 'admin'
		);
translate();

?>