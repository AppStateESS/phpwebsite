<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
translate('layout');
$link[] = array('label'       => _('Layout'),
		 'restricted'  => TRUE,
		 'url'         => 'index.php?module=layout&action=admin',
		 'description' => _('Control the layout of your site.'),
		 'image'       => 'layout.png',
		 'tab'         => 'admin'
		 );
translate();
?>