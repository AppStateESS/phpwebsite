<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */
translate('menu');
$link[] = array('label'       => _('Menu'),
		'restricted'  => TRUE,
		'url'         => 'index.php?module=menu',
		'description' => _('Controls the layout and positioning of your menus.'),
		'image'       => 'menu.png',
		'tab'         => 'content'
		);
translate();

?>