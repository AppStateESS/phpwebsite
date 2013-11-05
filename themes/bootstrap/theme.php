<?php
javascript('jquery');
Layout::plug(Layout::getPageTitle(TRUE), 'SITE_TITLE');
Layout::addJSHeader('<script type="text/javascript" src="' . PHPWS_SOURCE_HTTP . 'themes/bootstrap/js/bootstrap.min.js"></script>', 'bootstrap');
Layout::addJSHeader('<script type="text/javascript" src="' . PHPWS_SOURCE_HTTP . 'themes/bootstrap/js/modal_fix.js"></script>', 'fix');

$key = Key::getCurrent();
if (!is_null($key)) {
    Layout::plug('active', 'CONTENT_PAGE');
}
?>
