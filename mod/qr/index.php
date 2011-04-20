<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
if (isset($_GET['id']) && isset($_GET['size'])) {
    $qr = new QR($_GET['id'], $_GET['size']);
    echo $qr->get();
    exit();
} else {
    PHPWS_Core::errorPage('404');
}
?>
