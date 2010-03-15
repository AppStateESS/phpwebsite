<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

$tabs[] = array('id' => 'content',
		'title' => dgettext('controlpanel', 'Content'),
		'link'  => 'index.php?module=controlpanel',
);

$tabs[] = array('id' => 'admin',
		'title' => dgettext('controlpanel', 'Administration'),
		'link'  => 'index.php?module=controlpanel',
);

$tabs[] = array('id' => 'developer',
		'title' => dgettext('controlpanel', 'Developer'),
		'link'  => 'index.php?module=controlpanel',
);

$tabs[] = array('id' => 'unsorted',
		'title' => dgettext('controlpanel', 'Unsorted'),
		'link'  => 'index.php?module=controlpanel',
);

$link[] = array('label'       => dgettext('controlpanel', 'Control Panel'),
		'restricted'  => TRUE,
		'url'         => 'index.php?module=controlpanel&amp;action=admin',
		'description' => dgettext('controlpanel', 'Allow manipulation of the control panel.'),
		'image'       => 'controlpanel.png',
		'tab'         => 'admin'
		);

		?>