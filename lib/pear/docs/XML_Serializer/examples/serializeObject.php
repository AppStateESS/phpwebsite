<?PHP
/**
 * This is just a basic example that shows
 * how objects can be serialized so they can
 * be fully restored later.
 *
 * @author Stephan Schmidt <schst@php.net>
 */

/**
 * Example class that implements __sleep()
 *
 * @package    XML_Serializer
 * @subpackage Examples
 */
class MyClass
{
    var $foo = 'This is foo.';
    var $bar = 'This is bar.';
    
    function __sleep()
    {
        return array('foo');
    }
}

error_reporting(E_ALL);

/**
 * Load XML_Serializer
 */
require_once 'XML/Serializer.php';


// this is just to get a nested object
$pearError = PEAR::raiseError('This is just an error object',123);

$options = array(
                    XML_SERIALIZER_OPTION_INDENT      => '    ',
                    XML_SERIALIZER_OPTION_LINEBREAKS  => "\n",
                    XML_SERIALIZER_OPTION_DEFAULT_TAG => 'unnamedItem',
                    XML_SERIALIZER_OPTION_TYPEHINTS   => true
                );

$foo = new stdClass();
$foo->value = "My value";
$foo->error = $pearError;
$foo->xml   = "cool";

$foo->obj	= new MyClass();
$foo->arr   = array();
$foo->zero  = 0;


$serializer = &new XML_Serializer($options);

$result = $serializer->serialize($foo);

if( $result === true ) {
	$xml = $serializer->getSerializedData();
}

echo '<pre>';
echo htmlspecialchars($xml);
echo '</pre>';
?>