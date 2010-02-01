<?php

require_once 'HTML/Template/Flexy.php';
// simple testsuite..

function compilefile($file,$data =array(),$options=array(),$elements=array()) {
    
    $options = $options + array(
        
        'templateDir'   =>  dirname(__FILE__) .'/templates',            // where are your templates
        'forceCompile'  =>  true,  // only suggested for debugging
        'fatalError'    =>  HTML_TEMPLATE_FLEXY_ERROR_RETURN,  // only suggested for debugging
        'url_rewrite'   => 'images/:/myproject/images/',
        'compileDir'    =>  dirname(__FILE__) .'/results1',
    );

// basic options..
    echo "\n\n===Compiling $file===\n\n";
    $options['compileDir']    =  dirname(__FILE__) .'/results1';
    
    $x = new HTML_Template_Flexy($options);
    $res = $x->compile($file);
    if ($res !== true) {
        echo "===Compile failure==\n".$res->toString() . "\n";
        return;
    }
    echo "\n\n===Compiled file: $file===\n";
    echo file_get_contents($x->compiledTemplate);
    if (!empty($options['show_elements'])) {
        print_r($x->getElements());
    }
    if (!empty($options['show_words'])) {
        print_r(unserialize(file_get_contents($x->gettextStringsFile)));
    }
    
    echo "\n\n===With data file: $file===\n";
    $data = (object)$data;
    $x->outputObject($data,$elements);
    
}
    
    