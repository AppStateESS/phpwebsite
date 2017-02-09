<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

$tabs[] = array('id' => 'my_page',
                'title' => 'My Page',
		'link'  => 'index.php?module=users&amp;action=user',
);

$link[] = array('label'       => 'User Administration',
		'restricted'  => TRUE,
		'url'         => 'index.php?module=users&amp;action=admin',
		'description' => dgettext('users', 'Lets you create and edit users and groups.'),
		'image'       => 'users.png',
		'tab'         => 'admin'
		);
