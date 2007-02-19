<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */
translate('search');
$link[] = array('label'       => _('Search'),
		'restricted'  => TRUE,
		'url'         => 'index.php?module=search&amp;tab=keyword',
		'description' => _('Administrate and see information on searches.'),
		'image'       => 'search.png',
		'tab'         => 'admin'
		);
translate();

?>