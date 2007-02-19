<?php
  /**
   * @version $Id$
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */
translate('rss');
$link[] = array('label'       => _('RSS Feeds'),
		'restricted'  => TRUE,
		'url'         => 'index.php?module=rss&amp;tab=channels',
		'description' => _('Administrative panel for setting RSS feeds.'),
		'image'       => 'rss.png',
		'tab'         => 'admin'
		);
translate();

?>