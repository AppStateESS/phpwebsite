<?php
/* Mini test suite */
require_once 'HTML/Template/Flexy.php';
//print_r($_SERVER['argv']);

if (!isset($_SERVER['argv'][1])) {
    $files = array(dirname(__FILE__) . '/index.tpl');
} else {
    $files =$_SERVER['argv'];
    array_shift($files);
}
//print_r($files);
foreach($files as $file) {
    $dir = dirname($file);
    $x = new HTML_Template_Flexy(array(
                    'compileDir'    =>  dirname(__FILE__) ,      // where do you want to write to..
                    'templateDir'   =>  $dir ,     // where are your templates
                    'locale'        => 'en',    // works with gettext
                    'forceCompile'  =>  true,  // only suggested for debugging
                    'debug'         => false,   // prints a few errors
                    'nonHTML'       => false,  // dont parse HTML tags (eg. email templates)
                    'allowPHP'      => false,   // allow PHP in template
                    'compiler'      => 'SmartyConvertor', // which compiler to use.
                    'compileToString' => true,    // returns the converted template (rather than actually 
                                                   // converting to PHP.
                    'filters'       => array(),    // used by regex compiler..
                    'numberFormat'  => ",2,'.',','",  // default number format  = eg. 1,200.00 ( {xxx:n} )
                    'flexyIgnore'   => 0        // turn on/off the tag to element code
                ));
    
    echo $x->compile(basename($file));
}