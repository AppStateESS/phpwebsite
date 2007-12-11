<?php

/**
 * @version $Id$
 * @author  Steven Levin <steven at NOSPAM tux[dot]appstate[dot]edu>
 */

/* Labels */
$label     = dgettext('photoalbum', 'Name');
$editor    = dgettext('photoalbum', 'Editor');
$updated   = dgettext('photoalbum', 'Updated');
$desc      = dgettext('photoalbum', 'Description');
$thumbnail = dgettext('photoalbum', 'Thumbnail');
$short     = dgettext('photoalbum', 'Short');
$hidden    = dgettext('photoalbum', 'Hidden');

$lists = array('albums'     => 'approved=\'1\'',
	       'description'=> 'blurb IS NULL OR blurb=\'\'');

$templates = array('albums'     => 'albums',
		   'description'=> 'description');

$tables = array('albums'     => 'mod_photoalbum_albums',
		'description'=> 'mod_photoalbum_photos');

$albumsColumns = array('label'  => $label,
		       'blurb1' => $desc,
		       'updated'=> $updated,
		       'id'     => NULL,
		       'blurb0' => NULL,
		       'image'  => NULL);

$albumsActions = array();

$albumsPermissions = array();

$albumsPaging = array('op'      => 'PHPWS_AlbumManager_op=list',
		      'limit'   => 10,
		      'section' => 1,
		      'limits'  => array(5,10,20,50),
		      'back'    => '&#60;&#60;',
		      'forward' => '&#62;&#62;');

$descriptionColumns = array('thumbnail' => $thumbnail,
			    'label'     => $short,
			    'updated'   => $updated,
			    'hidden'    => $hidden,
			    'id'        => NULL);

$descriptionActions = array();

$descriptionPermissions = array();

$descriptionPaging = array('op'      => 'PHPWS_Album_op=desc',
			   'limit'   => 10,
			   'section' => 1,
			   'limits'  => array(5,10,20,50),
			   'back'    => '&#60;&#60;',
			   'forward' => '&#62;&#62;');

?>