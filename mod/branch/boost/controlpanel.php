<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */
translate('branch');
$link[] = array('label'       => _('Branch'),
		'restricted'  => TRUE,
		'url'         => 'index.php?module=branch',
		'description' => _('Install and update branch sites.'),
		'image'       => 'branch.png',
		'tab'         => 'admin'
		);
translate();

?>