<?php

/**
 * Unit Test for \Request
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

use \Request;

class RequestTest extends PHPUnit_Framework_TestCase
{
    public function testUrlSanitization()
    {
        // URLs that should not be mutated
        $this->assertNoMutate('/testing');
        $this->assertNoMutate('/testing/a/b/c/1/2/3');

        // URLs that should lose trailing slash
        $this->assertSanitizedResult('/testing/','/testing');
        $this->assertSanitizedResult('/testing/a/b/c/1/2/3/','/testing/a/b/c/1/2/3');

        // URLs that should gain preceding slash
        $this->assertSanitizedResult('testing','/testing');
        $this->assertSanitizedResult('testing/a/b/c/1/2/3','/testing/a/b/c/1/2/3');

        // URLs that should both gain and lose a slash
        $this->assertSanitizedResult('testing/', '/testing');
        $this->assertSanitizedResult('testing/a/b/c/1/2/3/','/testing/a/b/c/1/2/3');

        // The Root URL should not change
        $this->assertNoMutate('/');

        // Repeated slashes should be consolidated into one slash
        $this->assertSanitizedResult('///', '/');
        $this->assertSanitizedResult('///herp//////////mcderp', '/herp/mcderp');
        $this->assertSanitizedResult('/////////this', '/this');

        // Repeated slashes consolidation followed by trailing/preceding 
        // slash modification
        $this->assertSanitizedResult('herp//////mcderp', '/herp/mcderp');
        $this->assertSanitizedResult('this/////', '/this');
        $this->assertSanitizedResult('////this////', '/this');

        // Instances of /./ should be removed from the URL
        $this->assertSanitizedResult('./derp', '/derp');
        $this->assertSanitizedResult('/herp/./derp', '/herp/derp');
        $this->assertSanitizedResult('././././././this', '/this');
        $this->assertSanitizedResult('a/././././././b/././././././c', '/a/b/c');
        $this->assertSanitizedResult('herp//////./////.////.///////.///////./derp', '/herp/derp');

        // Dots should NOT be touched if they are part of a name
        $this->assertNoMutate('/.name.');
        $this->assertNoMutate('/.name');
        $this->assertNoMutate('/name.');
        $this->assertNoMutate('/.name./b.');
        $this->assertNoMutate('/.name/.b');
    }

    public function assertSanitizedResult($pre, $post)
    {
        $r = new Request($pre, 'GET');
        $this->assertEquals($r->getUrl(), $post);
    }

    public function assertNoMutate($pre)
    {
        $r = new Request($pre, 'GET');
        $this->assertEquals($r->getUrl(), $pre);
    }
}

?>
