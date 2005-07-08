<?php

$tabs[] = array('title' => _('My Page'),
		'label' => 'my_page',
		'link'  => 'index.php?module=users&amp;action=user',
		);

$link[] = array('label'       => _('User Administration'),
		'restricted'  => TRUE,
		'url'         => 'index.php?module=users&amp;action=admin',
		'description' => _('Lets you create and edit users and groups.'),
		'image'       => 'users.png',
		'tab'         => 'admin'
		);

?>