<?php

require_once 'PHPUnit.php';
require_once 'HTTP/Download.php';
require_once 'HTTP/Request.php';

class HTTP_DownloadTest extends PHPUnit_TestCase {

    function HTTP_DownloadTest($name)
    {
        $this->PHPUnit_TestCase($name);
    } 

    function setUp()
    {
        $this->testScript = 'http://local/www/mike/pear/HTTP_Download/tests/send.php';
    } 

    function tearDown()
    {
    } 

    function testHTTP_Download()
    {
        $this->assertTrue(is_a($h = &new HTTP_Download, 'HTTP_Download'));
        $this->assertTrue(is_a($h->HTTP, 'HTTP_Header'));
        unset($h);
    } 

    function testsetFile()
    {
        $h = &new HTTP_Download;
        $this->assertFalse(PEAR::isError($h->setFile('data.txt')), '$h->setFile("data.txt")');
        $this->assertEquals(realpath('data.txt'), $h->file, '$h->file == "data.txt');
        $this->assertTrue(PEAR::isError($h->setFile('nonexistant', false)), '$h->setFile("nonexistant")');
        unset($h);
    } 

    function testsetData()
    {
        $h = &new HTTP_Download;
        $this->assertTrue(null === $h->setData('foo'), 'null === $h->setData("foo")');
        $this->assertEquals('foo', $h->data, '$h->data == "foo"');
        unset($h);
    } 

    function testsetResource()
    {
        $h = &new HTTP_Download;
        $this->assertFalse(PEAR::isError($h->setResource($f = fopen('data.txt', 'r'))), '$h->setResource(fopen("data.txt","r"))');
        $this->assertEquals($f, $h->handle, '$h->handle == $f');
        fclose($f); $f = -1;
        $this->assertTrue(PEAR::isError($h->setResource($f)), '$h->setResource($f = -1)');
        unset($h);
    } 

    function testsetGzip()
    {
        $h = &new HTTP_Download;
        $this->assertFalse(PEAR::isError($h->setGzip(false)), '$h->setGzip(false)');
        $this->assertFalse($h->gzip, '$h->gzip');
        if (PEAR::loadExtension('zlib')) {
            $this->assertFalse(PEAR::isError($h->setGzip(true)), '$h->setGzip(true) without ext/zlib');
            $this->assertTrue($h->gzip, '$h->gzip');
        } else {
            $this->assertTrue(PEAR::isError($h->setGzip(true)), '$h->setGzip(true) with ext/zlib');
            $this->assertFalse($h->gzip, '$h->gzip');
        }
        unset($h);
    } 

    function testsetContentType()
    {
        $h = &new HTTP_Download;
        $this->assertFalse(PEAR::isError($h->setContentType('text/html;charset=iso-8859-1')), '$h->setContentType("text/html;charset=iso-8859-1")');
        $this->assertTrue(PEAR::isError($h->setContentType('##++***!งงงง?ฐฐ^^}][{')), '$h->setContentType("some weird characters")');
        $this->assertEquals('text/html;charset=iso-8859-1', $h->headers['Content-Type'], '$h->headers["Content-Type"] == "text/html;charset=iso-8859-1"');
        unset($h);
    } 

    function testguessContentType()
    {
        $h = &new HTTP_Download(array('file' => 'data.txt'));
        $e = $h->guessContentType();
        if (PEAR::isError($e) && $e->getCode() != HTTP_DOWNLOAD_E_NO_EXT_MMAGIC) {
            $this->assertTrue(false, $e->getMessage());
        }
        unset($h, $e);
    } 

    function _send($op)
    {
        $r = &new HTTP_Request($this->testScript);
        foreach (array('file', 'resource', 'data') as $what) {
            $r->reset($this->testScript);
            
            // without range
            $r->addQueryString('op', $op);
            $r->addQueryString('what', $what);
            $r->sendRequest();
            $this->assertEquals(200, $r->getResponseCode(), 'HTTP 200 Ok');
            $this->assertEquals(str_repeat('1234567890',10), $r->getResponseBody(), $what);
            
            // range 1-5
            $r->addHeader('Range', 'bytes=1-5');
            $r->sendRequest();
            $this->assertEquals(206, $r->getResponseCode(), 'HTTP 206 Partial Content');
            $this->assertEquals('23456', $r->getResponseBody(), $what);
            
            // range -5
            $r->addHeader('Range', 'bytes=-5');
            $r->sendRequest();
            $this->assertEquals(206, $r->getResponseCode(), 'HTTP 206 Partial Content');
            $this->assertEquals('67890', $r->getResponseBody(), $what);
            
            // range 95-
            $r->addHeader('Range', 'bytes=95-');
            $r->sendRequest();
            $this->assertEquals(206, $r->getResponseCode(), 'HTTP 206 Partial Content');
            $this->assertEquals('67890', $r->getResponseBody(), $what);
            $this->assertTrue(preg_match('/^bytes 95-\d+/', $r->getResponseHeader('content-range')), 'bytes keyword in Content-Range header');
            
            // multiple range
            $r->addHeader('Range', 'bytes=2-23,45-51,22-46');
            $r->sendRequest();
            $this->assertEquals(206, $r->getResponseCode(), 'HTTP 206 Partial Content');
            $this->assertTrue(preg_match('/^multipart\/byteranges; boundary=HTTP_DOWNLOAD-[a-f0-9.]{23}$/', $r->getResponseHeader('content-type')), 'Content-Type header: multipart/byteranges');
            $this->assertTrue(preg_match('/Content-Range: bytes 2-23/', $r->getResponseBody()), 'bytes keyword in Content-Range header');
        }
        unset($r);
    } 
    
    function testsend()
    {
        $this->_send('send');
    }
    
    function teststaticSend()
    {
        $this->_send('static');
    } 

    function testsendArchive()
    {
        $r = &new HTTP_Request($this->testScript);
        foreach (array('tar', 'tgz', 'zip', 'bz2') as $type) {
            $r->addQueryString('type', $type);
            $r->addQueryString('op', 'arch');
            
            $r->addQueryString('what', 'data.txt');
            $r->sendRequest();
            $this->assertEquals(200, $r->getResponseCode(), 'HTTP 200 Ok');
            $this->assertTrue($r->getResponseHeader('content-length') > 0, 'Content-Length > 0');
            $this->assertTrue(preg_match('/application\/x-(tar|gzip|bzip2|zip)/', $t = $r->getResponseHeader('content-type')), 'Reasonable Content-Type for '. $type .' (actual: '. $t .')');
        }
        unset($r);
    } 

} 

$suite  = &new PHPUnit_TestSuite('HTTP_DownloadTest');
$result = &PHPUnit::run($suite);
echo $result->toString();

?>