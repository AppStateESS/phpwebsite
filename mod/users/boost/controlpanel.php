<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

$tabs[] = array('title' => dgettext('users', 'My Page'),
		'label' => 'my_page',
		'link'  => 'index.php?module=users&amp;action=user',
		);

$link[] = array('label'       => dgettext('users', 'User Administration'),
		'restricted'  => TRUE,
		'url'         => 'index.php?module=users&amp;action=admin',
		'description' => dgettext('users', 'Lets you create and edit users and groups.'),
		'image'       => 'users.png',
		'tab'         => 'admin'
		);
?>