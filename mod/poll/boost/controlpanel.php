<?php
  /**
   * @version $Id: controlpanel.php 5613 2008-02-22 19:53:46Z matt $
   * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
   */

$link[] = array('label'       => dgettext('poll', 'Poll'),
		'restricted'  => TRUE,
		'url'         => 'index.php?module=poll&action=ShowPollAdmin',
		'description' => dgettext('poll', 'Poll your users on important items.'),
		'image'       => 'poll.png',
		'tab'         => 'content'
		);


?>
