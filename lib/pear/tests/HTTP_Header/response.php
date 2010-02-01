<?php
require_once 'HTTP/Header.php';

$h = &new HTTP_Header;
$s = 200;

foreach ($_GET as $header => $value) {
    if (!strcasecmp('redirect', $header)) {
        HTTP_Header::redirect($value);
    }
    if (strcasecmp('status', $header)) {
        $h->setHeader($header, $value);
    } else {
        $s = $value;
    }
}
$h->sendHeaders();
$h->sendStatusCode($s);
?>
