<?php

/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at appstate dot edu>
 */

$link[] = array('label'       => 'Pulse',
		'restricted'  => TRUE,
		'url'         => 'index.php?module=pulse&amp;aop=main',
		'description' => dgettext('pulse', 'Used by other modules to schedule processes.'),
		'image'       => 'pulse.png',
		'tab'         => 'admin'
		);
		?>