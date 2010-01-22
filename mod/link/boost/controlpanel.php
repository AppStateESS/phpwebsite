<?php
  /**
   * @version $Id: controlpanel.php 5613 2008-02-22 19:53:46Z matt $
   * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
   */

$link[] = array('label'       => dgettext('Link', 'link'),
		'restricted'  => TRUE,
		'url'         => 'index.php?module=link&action=ShowLinkAdmin',
		'description' => dgettext('link', 'Link to other websites and gather statistics.'),
		'image'       => 'link.png',
		'tab'         => 'content'
		);


?>
