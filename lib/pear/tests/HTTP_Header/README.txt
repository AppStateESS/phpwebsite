Running the TestSuite
=====================
$Id: README.txt,v 1.2 2004/11/10 15:55:21 mike Exp $

Copy response.php and cacheresponse.php into an
accessible webfolder and adjust the script locations
in the setUp() methods of header.php and header_cache.php

Now run header.php and header_cache.php with PHPUnit
and HTTP_Request installed.  The caching test will
take about 10 seconds.

Please report any issues at http://pear.php.net/bugs
