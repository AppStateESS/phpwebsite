--TEST--
PHP Backend XML-RPC server Validator1 test (nestedStructTest)
--FILE--
<?php
class TestServer {
    /**
     * test function
     *
     * see http://www.xmlrpc.com/validator1Docs
     *
     * @param array $struct
     * @return int result
     */
    public static function nestedStructTest($struct) {
    	// just to avoir problems with numeric indexes...
    	$struct2 = array();
    	while (list($key, $year) = each($struct)) {
    		if ($key=='2000') {
    			while (list($key2, $month) = each($year)) {
    				if ($key2=='04') {
    					while (list($key3, $day) = each($month)) {
    						if ($key3=='01') {
    							return $day['moe'] + $day['larry'] + $day['curly'];
    						}
    					}
    				}
    			} 
    		}
    	}
    }
}

set_include_path(realpath(dirname(__FILE__) . '/../../../../') . PATH_SEPARATOR . get_include_path());
require_once 'XML/RPC2/Server.php';
$options = array(
	'prefix' => 'validator1.',
	'backend' => 'Php'
);

$server = XML_RPC2_Server::create('TestServer', $options);
$GLOBALS['HTTP_RAW_POST_DATA'] = <<<EOS
<?xml version="1.0" encoding="iso-8859-1"?>
<methodCall>
<methodName>validator1.nestedStructTest</methodName>
<params>
 <param>
  <value>
   <struct>
    <member>
     <name>1999</name>
     <value>
      <struct>
       <member>
        <name>04</name>
        <value>
         <array>
          <data/>
         </array>
        </value>
       </member>
      </struct>
     </value>
    </member>
    <member>
     <name>2000</name>
     <value>
      <struct>
       <member>
        <name>04</name>
        <value>
         <struct>
          <member>
           <name>01</name>
           <value>
            <struct>
             <member>
              <name>moe</name>
              <value>
               <int>12</int>
              </value>
             </member>
             <member>
              <name>larry</name>
              <value>
               <int>14</int>
              </value>
             </member>
             <member>
              <name>curly</name>
              <value>
               <int>9</int>
              </value>
             </member>
            </struct>
           </value>
          </member>
         </struct>
        </value>
       </member>
      </struct>
     </value>
    </member>
    <member>
     <name>2001</name>
     <value>
      <struct>
       <member>
        <name>04</name>
        <value>
         <array>
          <data/>
         </array>
        </value>
       </member>
      </struct>
     </value>
    </member>
   </struct>
  </value>
 </param>
</params>
</methodCall>
EOS
;
$response = $server->getResponse();
$result = (XML_RPC2_Backend_Php_Response::decode(simplexml_load_string($response)));
var_dump($result);

?>
--EXPECT--
int(35)
