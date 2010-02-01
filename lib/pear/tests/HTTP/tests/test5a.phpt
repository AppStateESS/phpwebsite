--TEST--
absoluteURI() URL: https://example.org:8443/subdir/test.php?abc=123
--GET--
--ENV--
HTTP_HOST=example.org
HTTPS=on
SERVER_NAME=example.org
QUERY_STRING=abc=123
SERVER_PORT=8443
REQUEST_URI=/subdir/test.php?abc=123
SCRIPT_NAME=/subdir/test.php
--FILE--
<?php
/**
 * This test checks that absoluteURI() still works with HTTP / HTTPS
 *
 * PHP version 4 and 5
 *
 * @category HTTP
 * @package  HTTP
 * @author   Philippe Jausions <jausions@php.net>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://pear.php.net/package/HTTP
 */
require 'absoluteURI.inc';

?>
--EXPECT--
||                   => https://example.org:8443/subdir/test.php?abc=123
?new=value||         => https://example.org:8443/subdir/test.php?new=value
#anchor||            => https://example.org:8443/subdir/test.php?abc=123#anchor
/page.html||         => https://example.org:8443/page.html
page.html||          => https://example.org:8443/subdir/page.html
page.html|http|      => http://example.org/subdir/page.html
page.html|http|80    => http://example.org/subdir/page.html
page.html|http|8080  => http://example.org:8080/subdir/page.html
page.html|https|     => https://example.org/subdir/page.html
page.html|https|443  => https://example.org/subdir/page.html
page.html||8080      => https://example.org:8080/subdir/page.html
page.html|https|8888 => https://example.org:8888/subdir/page.html
