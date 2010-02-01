--TEST--
negotiateMimeType()
--GET--
--ENV--
HTTP_HOST=example.org
SERVER_NAME=example.org
QUERY_STRING=
SERVER_PORT=80
HTTPS=off
REQUEST_URI=/subdir/test.php
SCRIPT_NAME=/subdir/test.php
HTTP_ACCEPT=text/html,application/xhtml+xml,application/xml;q=0.9,text/*;q=0.7,*/*,image/gif; q=0.8, image/jpeg; q=0.6, image/* 
--FILE--
<?php
/**
 * This test checks for MIME type negotiation
 *
 * PHP version 4 and 5
 *
 * @category HTTP
 * @package  HTTP
 * @author   Philippe Jausions <jausions@php.net>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://pear.php.net/package/HTTP
 */
require_once  'HTTP.php';

// The --ENV-- Accept sets the following order
// (preferred types first)
// 1. text/html
// 2. application/xhtml+xml
// 3. application/xml
// 4. image/gif
// 5. text/* (any text type)
// 6. image/jpeg
// 7. image/* (any image type)
// 8. */* (any type)

$sets = array( 
    1 => array(
        'image/gif', 
        'image/png',
        'application/xhtml+xml',
        'application/xml',
        'text/html',
        'image/jpeg',
        'text/plain',
    ),
    2 => array(
        'image/gif', 
        'image/png',
        'application/xhtml+xml',
        'application/xml',
        'image/jpeg',
        'text/plain',
    ),
    3 => array(
        'image/gif', 
        'image/png',
        'application/xml',
        'image/jpeg',
        'text/plain',
    ),
    4 => array(
        'image/gif', 
        'image/png',
        'image/jpeg',
        'text/plain',
    ),
    5 => array(
        'image/png',
        'image/jpeg',
        'text/plain',
    ),
    6 => array(
        'image/png',
        'image/jpeg',
    ),
    7 => array(
        'image/png',
    ),
    8 => array(
        'audio/midi',
    ),
    9 => array(
    ),
);
foreach ($sets as $i => $supported) {
    echo $i.' => '.HTTP::negotiateMimeType($supported, 'application/octet-stream')
         ."\n";
}

?>
--EXPECT--
1 => text/html
2 => application/xhtml+xml
3 => application/xml
4 => image/gif
5 => text/plain
6 => image/jpeg
7 => image/png
8 => audio/midi
9 => application/octet-stream
