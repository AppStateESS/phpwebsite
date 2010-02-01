<?PHP
/**
 * This example shows how to create an RDF document
 * with a few lines of code.
 * This can also be done with mode => simplexml
 *
 * @author Stephan Schmidt <schst@php.net>
 * @see    serializeIndexedArray.php
 */
error_reporting(E_ALL);

require_once 'XML/Serializer.php';

$options = array(
                    XML_SERIALIZER_OPTION_INDENT           => '    ',
                    XML_SERIALIZER_OPTION_LINEBREAKS       => "\n",
                    XML_SERIALIZER_OPTION_TYPEHINTS        => false,
                    XML_SERIALIZER_OPTION_XML_DECL_ENABLED => true,
                    XML_SERIALIZER_OPTION_XML_ENCODING     => 'UTF-8',
					XML_SERIALIZER_OPTION_ROOT_NAME        => 'rdf:RDF',
                    XML_SERIALIZER_OPTION_ROOT_ATTRIBS     => array('version' => '0.91'),
					XML_SERIALIZER_OPTION_DEFAULT_TAG      => 'item',
                    XML_SERIALIZER_OPTION_ATTRIBUTES_KEY   => '_attributes'
                );

$serializer = new XML_Serializer($options);


$rdf    =   array(
					"channel" => array(
										"title" => "Example RDF channel",
										"link"  => "http://www.php-tools.de",
										"image"	=>	array(
															"title"	=> "Example image",
															"url"	=>	"http://www.php-tools.de/image.gif",
															"link"	=>	"http://www.php-tools.de"
														),
                                        "_attributes" => array( "rdf:about" => "http://example.com/foobar.html" ),
										array(
											"title"	=> "Example item",
											"link"	=> "http://example.com",
                                            "_attributes" => array( "rdf:about" => "http://example.com/foobar.html" )
										),
										array(
											"title"	=> "Another item",
											"link"	=> "http://example.com",
                                            "_attributes" => array( "rdf:about" => "http://example.com/foobar.html" )
										),
										array(
											"title"	=> "I think you get it...",
											"link"	=> "http://example.com",
                                            "_attributes" => array( "rdf:about" => "http://example.com/foobar.html" )
										)
									)
                );

$result = $serializer->serialize($rdf);

if ($result === true) {
    echo "<pre>";
    echo htmlentities($serializer->getSerializedData());
    echo "</pre>";
}
?>