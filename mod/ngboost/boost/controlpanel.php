<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id: controlpanel.php 7326 2010-03-15 19:38:52Z matt $
 */


$link[] = array('label'       => dgettext('ngboost', 'ngBoost'),
		'restricted'  => TRUE,
		'url'         => 'index.php?module=ngboost&action=admin',
		'description' => dgettext('ngboost', 'Boost allows the installation and upgrading of modules.'),
		'image'       => 'boost.png',
		'tab'         => 'admin'
		);

		?>