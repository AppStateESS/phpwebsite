<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */
translate('block');
$link[] = array('label'       => _('Block'),
                'restricted'  => TRUE,
                'url'         =>
                'index.php?module=block',
		'description' => _('Create blocks of content.'),
		'image'       => 'block.png',
		'tab'         => 'content'
		);
translate();
?>