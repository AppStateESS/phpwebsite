<?php

$_SESSION['htmlarea_info']['dir'] = PHPWS_Core::getHomeDir()
     . 'images/' . PHPWS_Core::getCurrentModule() . '/';

$_SESSION['htmlarea_info']['url'] = PHPWS_Core::getHomeHttp()
     . 'images/' . PHPWS_Core::getCurrentModule() . '/';

Layout::addOnLoad('HTMLArea.init(); HTMLArea.onload = initDocument');

$data['VALUE'] = str_replace('./images/', PHPWS_Core::getHomeHttp() . 'images/', $data['VALUE']);

$default['INSERT_HTML'] = _('Insert HTML');
$default['HIGHLIGHT'] = _('Highlight Text');
$default['NAME'] = 'htmlarea';

?>