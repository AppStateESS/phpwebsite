<?php

  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at appstate dot edu>
   */

translate('blog');
$link[] = array('label'       => 'Blog',
		'restricted'  => TRUE,
		'url'         => 'index.php?module=blog&amp;action=admin',
		'description' => _('Post current thoughts, happenings, and discussions.'),
		'image'       => 'blog.png',
		'tab'         => 'content'
		);
translate();
?>