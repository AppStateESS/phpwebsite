<?PHP
/**
 * XML Serializer example
 *
 * This example demonstrates, how XML_Serializer is able
 * to serialize predefined values as the attributes of a tag
 *
 * @author  Stephan Schmidt <schst@php.net>
 */
error_reporting(E_ALL);

require_once 'XML/Serializer.php';

$options = array(
                    XML_SERIALIZER_OPTION_INDENT               => '    ',
                    XML_SERIALIZER_OPTION_LINEBREAKS           => "\n",
                    XML_SERIALIZER_OPTION_DEFAULT_TAG          => 'unnamedItem',
					XML_SERIALIZER_OPTION_SCALAR_AS_ATTRIBUTES => false,
                    XML_SERIALIZER_OPTION_ATTRIBUTES_KEY       => '_attributes',
                    XML_SERIALIZER_OPTION_CONTENT_KEY          => '_content'
                );

$data = array(
                'foo' => array(
                                '_attributes' => array( 'version' => '1.0', 'foo' => 'bar' ),
                                '_content'    => 'test & test'
                              ),
                'schst' => 'Stephan Schmidt'
            );

$serializer = new XML_Serializer($options);

$result = $serializer->serialize($data);

if ($result === true) {
	$xml = $serializer->getSerializedData();
    echo '<pre>';
    echo htmlspecialchars($xml);
    echo '</pre>';
} else {
	echo '<pre>';
	print_r($result);
	echo '</pre>';
}

?>