<?php

javascript('bootstrap');
Layout::plug(Layout::getPageTitle(TRUE), 'SITE_TITLE');
Layout::addJSHeader('<script type="text/javascript" src="' . PHPWS_SOURCE_HTTP . 'themes/bootstrap/javascript/format.js"></script>');

$key = Key::getCurrent();
if (!is_null($key)) {
    Layout::plug('active', 'CONTENT_PAGE');
}
?>
