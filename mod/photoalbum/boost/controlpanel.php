<?php

/**
 * @version $Id: controlpanel.php 9 2006-06-05 20:24:53Z matt $
 * @author  Steven Levin
 * @modified Matthew McNaney <mcnaney at gmail dot com>
 */

$link[0] = array ('label'       => 'Photo Albums',
                  'restricted'  => TRUE,
		  'url'         => 'index.php?module=photoalbum&amp;PHPWS_AlbumManager_op=list',
		  'description' => 'The Photo Album allows you to manage sets of image galleries.',
		  'image'       => 'photo.png',
		  'tab'         => 'content');

?>