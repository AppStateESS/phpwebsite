<?php

/**
 * Generates the output for absoluteURI-related tests
 *
 * This file is called by other phpt files to run the same tests on
 * different $_ENV settings.
 *
 * @category HTTP
 * @package  HTTP
 * @author   Philippe Jausions <jausions@php.net>
 * @version  $Id: absoluteURI.inc,v 1.2 2008/02/09 07:46:32 jausions Exp $
 */
require_once 'HTTP.php';

// For 4th argument of HTTP::absoluteURI() method
if (!defined('HTTP_RELATIVETOSCRIPT')) {
    define('HTTP_RELATIVETOSCRIPT', true);
}

$tests = array(
    // page, protocol, port
    array(null, null, null),           // Current full URI
    array('?new=value', null, null),   // Append/replace query string
    array('#anchor', null, null),      // Anchor target to URI
    array('/page.html', null, null),   // Web root
    array('page.html', null, null),    // Relative
    array('page.html', 'http', null),  // Force HTTP
    array('page.html', 'http', 80),    // Force HTTP / default port
    array('page.html', 'http', 8080),  // Force HTTP / port 8080
    array('page.html', 'https', null), // Force HTTPS
    array('page.html', 'https', 443),  // Force HTTPS / default port
    array('page.html', null, 8080),    // Switch port (same protocol)
    array('page.html', 'https', 8888), // Force HTTPS / port 8888
    );

foreach ($tests as $test) {
    list($page, $protocol, $port) = $test;

    echo sprintf('%-20s', implode('|', $test)).' => '
         .HTTP::absoluteURI($page, $protocol, $port, HTTP_RELATIVETOSCRIPT)
         ."\n";
}

?>