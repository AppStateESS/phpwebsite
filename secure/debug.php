<?php
if($_SERVER['REMOTE_ADDR'] == '152.10.152.154') {
    $parts = explode('/',dirname($_SERVER['SCRIPT_URI']));
    array_pop($parts);
    array_push($parts, '');
    echo implode('/', $parts);
    phpinfo();
} else {
    header('Location: index.php');
}
?>
