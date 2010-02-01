<?php
/**
 * Test Case for HTTP_Header_Cache
 * 
 * Id$
 */

require_once 'PHPUnit.php';
require_once 'HTTP/Header/Cache.php';

class HTTP_Header_CacheTest extends PHPUnit_TestCase
{
    function HTTP_Header_CacheTest($name)
    {
        $this->PHPUnit_TestCase($name);
    } 

    function setUp()
    {
        $this->testScript = 'http://local/www/mike/pear/HTTP_Header/tests/cacheresponse.php';
    } 

    function tearDown()
    {
    } 

    function testHTTP_Header_Cache()
    {
        $this->assertTrue(is_a(new HTTP_Header_Cache, 'HTTP_Header_Cache'));
    } 

    function testgetCacheStart()
    {
        $c = &new HTTP_Header_Cache;
        $this->assertEquals(time(), $c->getCacheStart());
        $_SERVER['HTTP_IF_MODIFIED_SINCE'] = HTTP::Date(strtotime('yesterday'));
        $this->assertEquals($_SERVER['HTTP_IF_MODIFIED_SINCE'], HTTP::Date($c->getCacheStart()));
        unset($c, $_SERVER['HTTP_IF_MODIFIED_SINCE']);
    } 

    function testisOlderThan()
    {
        $c = &new HTTP_Header_Cache;
        $this->assertTrue($c->isOlderThan(1, 'second'));
        $this->assertTrue($c->isOlderThan(1, 'hour'));
        $_SERVER['HTTP_IF_MODIFIED_SINCE'] = HTTP::Date(time() - 3);
        $this->assertTrue($c->isOlderThan(1, 'second'));
        unset($c, $_SERVER['HTTP_IF_MODIFIED_SINCE']);
    } 

    function testisCached()
    {
        $c = &new HTTP_Header_Cache;
        $this->assertFalse($c->isCached(), 'no last modified');
        $_SERVER['HTTP_IF_MODIFIED_SINCE'] = HTTP::Date(strtotime('yesterday'));
        $this->assertTrue($c->isCached(), 'last modified header');
        $this->assertFalse($c->isCached(time()), 'last modified header (yesterday) and param (now)');
        $this->assertTrue($c->isCached(strtotime('last year')), 'last modified header (yesterday) and param (last year)');
        unset($c, $_SERVER['HTTP_IF_MODIFIED_SINCE']);
    } 

    function testexitIfCached()
    {
        require_once 'HTTP/Request.php';
        $r = &new HTTP_Request($this->testScript);
        $r->setMethod(HTTP_REQUEST_METHOD_GET);
        $r->addHeader('If-Modified-Since', HTTP::Date());
        $r->sendRequest();
        $this->assertEquals(304, $r->getResponseCode(), 'HTTP 304 Not Modified');
        $r->addHeader('If-Modified-Since', HTTP::Date(strtotime('yesterday')));
        $r->sendRequest();
        $this->assertEquals(200, $r->getResponseCode(), 'HTTP 200 Ok');
        unset($r);
    } 
    
    function testget()
    {
        require_once 'HTTP/Request.php';
        $r = &new HTTP_Request($this->testScript);
        $r->setMethod(HTTP_REQUEST_METHOD_GET);
        $r->sendRequest();
        $this->assertEquals(200, $r->getResponseCode(), 'HTTP 200 Ok (simple plain GET)');
        $r->addHeader('If-Modified-Since', $r->getResponseHeader('last-modified'));
        sleep(3);
        $r->sendRequest();
        $this->assertEquals(304, $r->getResponseCode(), 'HTTP 304 Not Modified (GET with If-Modified-Since set to Last-Modified of prior request');
        unset($r);
    }

    function testhead()
    {
        require_once 'HTTP/Request.php';
        $r = &new HTTP_Request($this->testScript);
        $r->setMethod(HTTP_REQUEST_METHOD_HEAD);
        $r->sendRequest();
        $this->assertEquals(200, $r->getResponseCode(), 'HTTP 200 Ok (simple plain GET)');
        $r->addHeader('If-Modified-Since', $r->getResponseHeader('last-modified'));
        sleep(3);
        $r->sendRequest();
        $this->assertEquals(304, $r->getResponseCode(), 'HTTP 304 Not Modified (GET with If-Modified-Since set to Last-Modified of prior request');
        unset($r);
    }

    function testpost()
    {
        require_once 'HTTP/Request.php';
        $r = &new HTTP_Request($this->testScript);
        $r->setMethod(HTTP_REQUEST_METHOD_GET);
        $r->sendRequest();
        $lm = $r->getResponseHeader('last-modified');
        $r->setMethod(HTTP_REQUEST_METHOD_POST);
        $r->sendRequest();
        $this->assertEquals(200, $r->getResponseCode(), 'HTTP 200 Ok (POST without If-Modified-Since)');
        $r->addHeader('If-Modified-Since', HTTP::Date(strtotime('yesterday')));
        $r->sendRequest();
        $this->assertEquals(200, $r->getResponseCode(), 'HTTP 200 Ok (POST with If-Modified-Since == yesterday)');
        $r->addHeader('If-Modified-Since', HTTP::Date(time() - 3));
        $r->sendRequest();
        $this->assertEquals(200, $r->getResponseCode(), 'HTTP 200 Ok (POST with If-Modified-Since == now)');
        $r->addHeader('If-Modified-Since', HTTP::Date($lm));
        sleep(3);
        $r->sendRequest();
        $this->assertEquals(200, $r->getResponseCode(), 'HTTP 200 Ok (POST with If-Modified-Since == Last-Modified)');
        $this->assertEquals(HTTP::Date(), $r->getResponseHeader('last-modified'), 'POST time() == Last-Modified');
        unset($r);
    }
} 

$suite  = &new PHPUnit_TestSuite('HTTP_Header_CacheTest');
$result = &PHPUnit::run($suite);
echo $result->toString();

?>
