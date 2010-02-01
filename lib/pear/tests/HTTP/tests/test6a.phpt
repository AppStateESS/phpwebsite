--TEST--
absoluteURI() URL: http://example.org/subdir/
--GET--
--ENV--
HTTP_HOST=example.org
SERVER_NAME=example.org
SERVER_PORT=80
REQUEST_URI=/subdir/
SCRIPT_NAME=/subdir/index.php
--FILE--
<?php
/**
 * This test checks that absoluteURI() still works even when the script name
 * is a default "index" file, and doesn't appear in the calling URI.
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
||                   => http://example.org/subdir/
?new=value||         => http://example.org/subdir/?new=value
#anchor||            => http://example.org/subdir/#anchor
/page.html||         => http://example.org/page.html
page.html||          => http://example.org/subdir/page.html
page.html|http|      => http://example.org/subdir/page.html
page.html|http|80    => http://example.org/subdir/page.html
page.html|http|8080  => http://example.org:8080/subdir/page.html
page.html|https|     => https://example.org/subdir/page.html
page.html|https|443  => https://example.org/subdir/page.html
page.html||8080      => http://example.org:8080/subdir/page.html
page.html|https|8888 => https://example.org:8888/subdir/page.html
