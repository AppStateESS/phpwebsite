<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

$tabs[] = array('id' => 'content',
		'title' => _('Content'),
		'link'  => 'index.php?module=controlpanel',
		);

$tabs[] = array('id' => 'admin',
		'title' => _('Administration'),
		'link'  => 'index.php?module=controlpanel',
		);

$tabs[] = array('id' => 'developer',
		'title' => _('Developer'),
		'link'  => 'index.php?module=controlpanel',
		);

$tabs[] = array('id' => 'unsorted',
		'title' => _('Unsorted'),
		'link'  => 'index.php?module=controlpanel',
		);

$link[] = array('label'       => _('Control Panel'),
		'restricted'  => TRUE,
		'url'         => 'index.php?module=controlpanel&amp;action=admin',
		'description' => _('Allow manipulation of the control panel.'),
		'image'       => 'controlpanel.png',
		'tab'         => 'admin'
		);


?>