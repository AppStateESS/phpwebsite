<?php

	$data['authkey']	  = Current_User::getAuthKey();
	$data['source_http']  = PHPWS_SOURCE_HTTP;
	$data['TYPE_TITLE']   = dgettext('core', 'FileType');
	$data['FOLDER_TITLE'] = dgettext('core', 'Folder');
	$data['FILES_TITLE']  = dgettext('core', 'Files');

	javascript('jquery');
	
?>