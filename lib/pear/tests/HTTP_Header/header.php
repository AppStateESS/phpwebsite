<?php
/**
 * TestCase for HTTP_Header
 * 
 * $Id: header.php,v 1.2 2004/07/15 13:11:13 mike Exp $
 */

require_once 'PHPUnit.php';
require_once 'HTTP/Header.php';

class HTTP_HeaderTest extends PHPUnit_TestCase
{
    function HTTP_HeaderTest($name)
    {
        $this->PHPUnit_TestCase($name);
    } 

    function setUp()
    {
        $this->testScript = 'http://local/www/mike/pear/HTTP_Header/tests/response.php';
    } 

    function tearDown()
    {
    } 

    function testHTTP_Header()
    {
        $h = &new HTTP_Header;
        $this->assertTrue(is_a($h, 'HTTP_Header'));
        $this->assertTrue($h->_httpVersion == 1.1 || $h->_httpVersion == 1.0);
        unset($h);
    } 

    function testsetHttpVersion()
    {
        $h = &new HTTP_Header;
        $this->assertFalse($h->setHttpVersion('foo'));
        $this->assertTrue($h->setHttpVersion(1.0));
        $this->assertTrue($h->setHttpVersion(1.1));
        $this->assertTrue($h->setHttpVersion(1));
        $this->assertTrue($h->setHttpVersion(1.111111111));
        $this->assertTrue($h->setHttpVersion('1'));
        $this->assertTrue($h->setHttpVersion('1.1'));
        $this->assertTrue($h->setHttpVersion('1.0000000000000'));
        $this->assertFalse($h->setHttpVersion(2));
        unset($h);
    } 

    function testgetHttpVersion()
    {
        $h = new HTTP_Header;
        $this->assertTrue($h->getHttpVersion() == 1.0 || $h->getHttpVersion() == 1.1, ' http version is 1.0 or 1.1');
        $h->setHttpVersion(1);
        $this->assertEquals(1, $h->getHttpVersion());
        $h->setHttpVersion(1.1);
        $this->assertEquals(1.1, $h->getHttpVersion());
        $h->setHttpVersion(2);
        $this->assertEquals(1.1, $h->getHttpVersion());
        unset($h);
    } 

    function testsetHeader()
    {
        $h = &new HTTP_Header;
        $this->assertFalse($h->setHeader(null), 'set null');
        $this->assertFalse($h->setHeader(''), ' set empty string');
        $this->assertFalse($h->setHeader(0), 'set 0');
        $this->assertTrue($h->setHeader('X-Foo', 'bla'), 'set X-Foo = bla');
        $this->assertFalse($h->setHeader('X-Array', array('foo')), 'set array');
        $this->assertFalse($h->setHeader('X-Object', new StdClass), 'set object');
        unset($h);
    } 

    function testgetHeader()
    {
        $h = &new HTTP_Header;
        $this->assertEquals('no-cache', $h->getHeader('Pragma'));
        $this->assertEquals('no-cache', $h->getHeader('pRaGmA'));
        $h->setHeader('X-Foo', 'foo');
        $this->assertEquals('foo', $h->getHeader('X-Foo'));
        $this->assertEquals('foo', $h->getHeader('x-FoO'));
        $this->assertTrue(is_array($h->getHeader()), 'test for array');
        $this->assertFalse($h->getHeader('Non-Existant'), 'test unset header');
        unset($h);
    } 

    function testsendHeaders()
    {
        require_once 'HTTP/Request.php';
        $r = &new HTTP_Request($this->testScript);
        $r->setMethod(HTTP_REQUEST_METHOD_GET);
        $r->addQueryString('X-Foo', 'blablubb');
        $r->sendRequest();
        $this->assertEquals('blablubb', $r->getResponseHeader('x-foo'));
        unset($h, $r);
    } 

    function testsendStatusCode()
    {
        require_once 'HTTP/Request.php';
        $r = &new HTTP_Request($this->testScript);
        $r->setMethod(HTTP_REQUEST_METHOD_GET);
        $r->sendRequest();
        $this->assertEquals(200, $r->getResponseCode(), 'test for response code 200');
        $r->addQueryString('status', 500);
        $r->sendRequest();
        $this->assertEquals(500, $r->getResponseCode(), 'test for response code 500');
        unset($h, $r);
    } 

    function testdateToTimestamp()
    {
        $h = &new HTTP_Header;
        $this->assertEquals(strtotime($d = HTTP::Date()), $h->dateToTimestamp($d));
        unset($h);
    } 

    function testredirect()
    {
        require_once 'HTTP/Request.php';
        $r = &new HTTP_Request($this->testScript, array('allowRedirects' => false));
        $r->setMethod(HTTP_REQUEST_METHOD_GET);
        $r->addQueryString('redirect', 'response.php?abc=123');
        $r->sendRequest();
        $this->assertEquals(302, $r->getResponseCode(), 'test for response code 302');
        $this->assertTrue(strstr($r->getResponseHeader('location'), 'response.php'));
        unset($h, $r);
    } 

    function testgetStatusType()
    {
        $h = &new HTTP_Header;
        $this->assertEquals(HTTP_HEADER_STATUS_INFORMATIONAL, $h->getStatusType(101));
        $this->assertEquals(HTTP_HEADER_STATUS_SUCCESSFUL, $h->getStatusType(206));
        $this->assertEquals(HTTP_HEADER_STATUS_REDIRECT, $h->getStatusType(301));
        $this->assertEquals(HTTP_HEADER_STATUS_CLIENT_ERROR, $h->getStatusType(404));
        $this->assertEquals(HTTP_HEADER_STATUS_SERVER_ERROR, $h->getStatusType(500));
        $this->assertFalse($h->getStatusType(8));
        unset($h);
    } 

    function testgetStatusText()
    {
        $h = &new HTTP_Header;
        $this->assertEquals(HTTP_HEADER_STATUS_100, '100 '. $h->getStatusText(100));
        $this->assertEquals(HTTP_HEADER_STATUS_200, '200 '. $h->getStatusText(200));
        $this->assertEquals(HTTP_HEADER_STATUS_300, '300 '. $h->getStatusText(300));
        $this->assertEquals(HTTP_HEADER_STATUS_302, '302 '. $h->getStatusText(302));
        $this->assertEquals(HTTP_HEADER_STATUS_401, '401 '. $h->getStatusText(401));
        $this->assertEquals(HTTP_HEADER_STATUS_400, '400 '. $h->getStatusText(400));
        $this->assertEquals(HTTP_HEADER_STATUS_500, '500 '. $h->getStatusText(500));
        $this->assertEquals(HTTP_HEADER_STATUS_102, '102 '. $h->getStatusText(102));
        $this->assertEquals(HTTP_HEADER_STATUS_404, '404 '. $h->getStatusText(404));
        $this->assertFalse($h->getStatusText(0));
        $this->assertFalse($h->getStatusText(800));
        unset($h);
    } 

    function testisInformational()
    {
        $h = &new HTTP_Header;
        $this->assertTrue($h->isInformational(100));
        $this->assertTrue($h->isInformational(101));
        $this->assertTrue($h->isInformational(102));
        $this->assertFalse($h->isInformational(404));
        unset($h);
    } 

    function testisSuccessful()
    {
        $h = &new HTTP_Header;
        $this->assertTrue($h->isSuccessful(200));
        $this->assertTrue($h->isSuccessful(201));
        $this->assertTrue($h->isSuccessful(202));
        $this->assertFalse($h->isSuccessful(404));
        unset($h);
    } 

    function testisRedirect()
    {
        $h = &new HTTP_Header;
        $this->assertTrue($h->isRedirect(300));
        $this->assertTrue($h->isRedirect(301));
        $this->assertTrue($h->isRedirect(302));
        $this->assertFalse($h->isRedirect(404));
        unset($h);
    } 

    function testisClientError()
    {
        $h = &new HTTP_Header;
        $this->assertTrue($h->isClientError(400));
        $this->assertTrue($h->isClientError(401));
        $this->assertTrue($h->isClientError(404));
        $this->assertFalse($h->isClientError(500));
        unset($h);
    } 

    function testisServerError()
    {
        $h = &new HTTP_Header;
        $this->assertTrue($h->isServerError(500));
        $this->assertTrue($h->isServerError(501));
        $this->assertTrue($h->isServerError(502));
        $this->assertFalse($h->isServerError(404));
        unset($h);
    } 

    function testisError()
    {
        $h = &new HTTP_Header;
        $this->assertTrue($h->isError(500));
        $this->assertTrue($h->isError(501));
        $this->assertTrue($h->isError(502));
        $this->assertFalse($h->isError(206));
        $this->assertTrue($h->isError(400));
        $this->assertTrue($h->isError(401));
        $this->assertTrue($h->isError(404));
        $this->assertFalse($h->isError(100));
        unset($h);
    } 
} 

$suite  = &new PHPUnit_TestSuite('HTTP_HeaderTest');
$result = &PHPUnit::run($suite);
echo $result->toString();

?>
