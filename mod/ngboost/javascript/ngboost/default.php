<?php

$data['authkey']   = Current_User::getAuthKey();
$data['ngboostmsg011'] = dgettext('ngboost','just');
$data['ngboostmsg012'] = dgettext('ngboost','modules to process');
$data['ngboostmsg020'] = dgettext('ngboost','Do you really want to purge the backup file?');
$data['ngboostmsg030'] = dgettext('ngboost','Do you really want to restore / overwrite?');
$data['ngboostmsg040'] = dgettext('ngboost','are you sure to uninstall');
$data['ngboostmsg050'] = dgettext('ngboost','are you sure to purge');

javascript('jquery');
Layout::addJSHeader('<script type="text/javascript" src="'.PHPWS_SOURCE_HTTP.'javascript/jquery/jquery.mb.browser.min.js"></script>', 'browser');

?>