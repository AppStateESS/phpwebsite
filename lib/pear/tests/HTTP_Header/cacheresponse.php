<?php
require_once 'HTTP/Header/Cache.php';
$h = &new HTTP_Header_Cache(1, 'hour');
$h->sendHeaders();
echo date('r');
?>
