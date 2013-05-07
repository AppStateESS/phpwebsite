<?php

javascript('bootstrap');
Layout::plug(Layout::getPageTitle(TRUE), 'SITE_TITLE');

$key = Key::getCurrent();
if(!is_null($key)) {
    Layout::plug('active', 'CONTENT_PAGE');
}

?>
