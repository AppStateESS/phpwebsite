<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */
translate('profiler');
$link[] = array('label'       => _('Profiler'),
		'restricted'  => TRUE,
		'url'         => 'index.php?module=profiler',
		'description' => _('Create profiles on individuals for display on site.'),
		'image'       => 'profile.png',
		'tab'         => 'content'
		);
translate();

?>