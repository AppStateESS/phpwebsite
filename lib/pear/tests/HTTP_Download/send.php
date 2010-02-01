<?php

require_once 'PEAR.php';
require_once 'HTTP/Download.php';

PEAR::setErrorHandling(PEAR_ERROR_PRINT);

$params = @$_GET['params'];

switch ($_GET['what'])
{
    case 'file':
        $params['file'] = 'data.txt';
    break;
    case 'resource':
        $params['resource'] = fopen('data.txt', 'rb');
    break;
    case 'data':
        $params['data'] = file_get_contents('data.txt');
    break;
}

switch ($_GET['op'])
{
    case 'static':
        HTTP_Download::staticSend($params);
    break;
    
    case 'send':
        $h = &new HTTP_Download;
        $h->setParams($params);
        $h->send();
    break;
    
    case 'arch':
        HTTP_Download::sendArchive('foo.'. $_GET['type'], $_GET['what'], $_GET['type']);
    break;
}

?>
