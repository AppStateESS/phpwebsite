<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors:  Alan Knowles <alan@akkbhome.com>                           |
// | Authors:  Tobias dot eberle at gmx dot de (include with vars)        |
// +----------------------------------------------------------------------+
//
// $Id: Flexy.php,v 1.14 2009/03/06 08:45:17 alan_k Exp $
//
//  Handler code for the <flexy: namespace
//

/**
* the <flexy:XXXX namespace
* 
* 
* at present it handles
*       <flexy:toJavascript flexy:prefix="Javascript_prefix"  javscriptName="PHPvar" .....>
*       <flexy:include src="xxx.htm">
*
*
*
* @version    $Id: Flexy.php,v 1.14 2009/03/06 08:45:17 alan_k Exp $
*/

class HTML_Template_Flexy_Compiler_Flexy_Flexy  {

        
    /**
    * Parent Compiler for 
    *
    * @var  object  HTML_Template_Flexy_Compiler  
    * 
    * @access public
    */
    var $compiler;

   
    /**
    * The current element to parse..
    *
    * @var object
    * @access public
    */    
    var $element;
    
    
    
    
    
    /**
    * toString - display tag, attributes, postfix and any code in attributes.
    * Relays into namspace::method to get results..
    *
    * 
    * @see parent::toString()
    */
    function toString($element) 
    {
        
        list($namespace,$method) = explode(':',$element->oTag);
        if (!strlen($method)) {
            return '';
        }
        // things we dont handle...
        if (!method_exists($this,$method.'ToString')) {
            return '';
        }
        return $this->{$method.'ToString'}($element);
        
    }
   /**
    * toJavascript handler
    * <flexy:toJavascript flexy:prefix="some_prefix_"  javascriptval="php.val" ....>
    * 
    * @see parent::toString()
    */
    
    function toJavascriptToString($element) 
    {
        $ret = $this->compiler->appendPhp( "require_once 'HTML/Javascript/Convert.php';");
        $ret .= $this->compiler->appendHTML("\n<script type='text/javascript'>\n");
        $prefix = ''. $element->getAttribute('FLEXY:PREFIX');
        
        
        foreach ($element->attributes as $k=>$v) {
            // skip directives..
            if (strpos($k,':')) {
                continue;
            }
            if ($k == '/') {
                continue;
            }
            $v = substr($v,1,-1);
            $ret .= $this->compiler->appendPhp(
                '$__tmp = HTML_Javascript_Convert::convertVar('.$element->toVar($v) .',\''.$prefix . $k.'\',true);'.
                'echo (is_a($__tmp,"PEAR_Error")) ? ("<pre>".print_r($__tmp,true)."</pre>") : $__tmp;');
            $ret .= $this->compiler->appendHTML("\n");
        }
        $ret .= $this->compiler->appendHTML("</script>");
        return $ret;
    }
    
    /**
    * toJavascript handler
    * <flexy:toJSON  javascriptval="php.val" ....>
    * 
    * @see parent::toString()
    */
    
    function toJSONToString($element) 
    {
        // maybe should use extnsion_exists....
        $ret = "";
        if (!function_exists('json_encode')) {
            $ret = $this->compiler->appendPhp( 
                'require_once "Services/JSON.php"; $_json = new Services_JSON();'
            );
        } 
        
        //$ret = $this->compiler->appendPhp( "require_once 'HTML/Javascript/Convert.php';");
        $ret .= $this->compiler->appendHTML("\n<script type='text/javascript'>\n");
        //$prefix = ''. $element->getAttribute('FLEXY:PREFIX');
        
        
        foreach ($element->attributes as $k=>$v) {
            // skip directives..
            if (strpos($k,':')) {
                continue;
            }
            if ($k == '/') {
                continue;
            }
            $v = substr($v,1,-1);
            if (function_exists('json_encode')) {
                $ret .= $this->compiler->appendPhp(
                    'echo "var '. $k .'=" . json_encode('.$element->toVar($v).') . ";\n";'
                );
                $ret .= $this->compiler->appendHTML("\n");
                continue;
            }
            $ret .= $this->compiler->appendPhp(
                    'echo "var '.$k.'=" . $_json->encode('.$element->toVar($v).') . ";\n";'
            );
           
            $ret .= $this->compiler->appendHTML("\n");
        }
        $ret .= $this->compiler->appendHTML("</script>");
        return $ret;
    }
    
    /**
    * include handler
    * <flexy:include src="test.html">
    * <flexy:include src="{test}">
    * <flexy:include src="{test}.html">
    * @see parent::toString()
    */
    function includeToString($element) 
    {
        // this is disabled by default...
        // we ignore modifier pre/suffix
    
    
    
       
        
        if (!isset($element->ucAttributes['SRC'])) {
            return $this->compiler->appendHTML("<B>Flexy:Include without a src=filename (Line: {$element->line})</B>");
        }
        $arg = $element->ucAttributes['SRC'];
         
        // it's a string so its easy to handle
        switch (true) {
            case is_string($arg):
                if ($arg == '""') {
                    return $this->compiler->appendHTML("<B>Flexy:Include src attribute is empty. (Line: {$element->line})</B>");
                }
                $arg = "'". $element->getAttribute('SRC')."'";
                break;
            
            case is_array($arg): // it's an array -> strings and variables possible
                $string = '"';
                foreach($arg as $item) {
                    //it's a string
                    if (is_string($item)) {
                        if ($item != '' && $item != '"' && $item != '""' && 
                            $item != "''") {
                            $string .= $item;
                        }
                    } else {
                        //it's a variable
                        if (is_a($item, 'HTML_Template_Flexy_Token_Var')) {
                            $value = $item->toVar($item->value);
                            if (is_a($value, 'PEAR_Error')) {
                                return $value;
                            }
                            $string .= "{{$value}}";
                        }
                    }
                }
                $arg = $string . '"';
                break;
            
            default:
            //something unexspected
                return HTML_Template_Flexy::raiseError(
                    ' Flexy:Include SRC needs a string or variable/method as value. '.
                    " Error on Line {$element->line} &lt;{$element->tag}&gt;",
                    null, HTML_TEMPLATE_FLEXY_ERROR_DIE); 
            
                
            
        }
 
        // ideally it would be nice to embed the results of one template into another.
        // however that would involve some complex test which would have to stat
        // the child templates anyway..
        // compile the child template....
        // output... include $this->options['compiled_templates'] . $arg . $this->options['locale'] . '.php'
        return $this->compiler->appendPHP( "\n".
                "\$x = new HTML_Template_Flexy(\$this->options);\n".
                "\$x->compile({$arg});\n".
                "\$_t = function_exists('clone') ? clone(\$t) : \$t;\n".
                "foreach(".$element->scopeVarsToArrayString(). "  as \$k) {\n" .
                "    if (\$k != 't') { \$_t->\$k = \$\$k; }\n" .
                "}\n" .
                "\$x->outputObject(\$_t, \$this->elements);\n"
            );
    
    }
    
    /**
    * Convert flexy tokens to HTML_Template_Flexy_Elements.
    *
    * @param    object token to convert into a element.
    * @return   object HTML_Template_Flexy_Element
    * @access   public
    */
    function toElement($element) 
    {
       return '';
    }
        
    
    /**
    * Handler for User defined functions in templates..
    * <flexy:function name="xxxxx">.... </flexy:block>  // equivilant to function xxxxx() { 
    * <flexy:function call="{xxxxx}">.... </flexy:block>  // equivilant to function {$xxxxx}() { 
    * <flexy:function call="xxxxx">.... </flexy:block>  // equivilant to function {$xxxxx}() { 
    * 
    * This will not handle nested blocks initially!! (and may cause even more problems with 
    * if /foreach stuff..!!
    *
    * @param    object token to convert into a element.
    * @access   public
    */
  
    
    function functionToString($element) 
    {
        
        if ($arg = $element->getAttribute('NAME')) {
            // this is a really kludgy way of doing this!!!
            // hopefully the new Template Package will have a sweeter method..
            $GLOBALS['_HTML_TEMPLATE_FLEXY']['prefixOutput']  .= 
                $this->compiler->appendPHP( 
                    "\nfunction _html_template_flexy_compiler_flexy_flexy_{$arg}(\$t,\$this) {\n").
                $element->compileChildren($this->compiler) .
                $this->compiler->appendPHP( "\n}\n");
                
                return '';
        }
        if (!isset($element->ucAttributes['CALL'])) {
            
            return HTML_Template_Flexy::raiseError(
                ' tag flexy:function needs an argument call or name'.
                " Error on Line {$element->line} &lt;{$element->tag}&gt;",
                         null,   HTML_TEMPLATE_FLEXY_ERROR_DIE);
        }
        // call is a  stirng : nice and simple..
        if (is_string($element->ucAttributes['CALL'])) {
            $arg = $element->getAttribute('CALL');
            return $this->compiler->appendPHP( 
                    "if (function_exists('_html_template_flexy_compiler_flexy_flexy_{$arg}')) " .
                    " _html_template_flexy_compiler_flexy_flexy_{$arg}(\$t,\$this);");
        }
        
        // we make a big assumption here.. - it should really be error checked..
        // that the {xxx} element is item 1 in the list... 
        $e=$element->ucAttributes['CALL'][1];
        $add = $e->toVar($e->value);
        if (is_a($add,'PEAR_Error')) {
            return $add;
        } 
        return $this->compiler->appendPHP(
            "if (function_exists('_html_template_flexy_compiler_flexy_flexy_'.{$add})) ".
            "call_user_func_array('_html_template_flexy_compiler_flexy_flexy_'.{$add},array(\$t,\$this));");
        
        
        
    }


    
    
   /** 
    /**
    * - A partial is a subtemplate to which you can pass variables.
    * - You can define variables for the partial as xml attributes
    * - You can provide context for the variables by adhering to the 
    *   convention 'subtemplateVarName="templateVarName"'
    * - See example below:
    *  
    * <flexy:partial src="test.html" subtemplateVar1="var1" 
    *   subtemplateVar2="object.var2" subtemplateVar3="#literal1#" />
    */
	function partialToString($element)
    {
        $src = $element->getAttribute('SRC');
        
        if (!$src)  {
            return $this->compiler->appendHTML("<B>Flexy:Subtemplate without a src=filename</B>");
        }
        
        /**
        * Define parameters for partial (if set)
        */
        $aAttribute = $element->getAttributes(); 

        
        if (!is_array($aAttribute)) {
            $aAttribute = array();
        }
        
        $aOutput = array();
            
        foreach ($aAttribute as $name => $value) {
            if ($name == 'src' || $name == '/') {
                continue;
            }
                
            $varName                = trim($name);
            $varVal                 = trim($value);
            $isLiteral              = preg_match('@\#(.*)\#@',$varVal);

            /**
            *   Provide supplied variables with subtemplate context
            * - Deal with string literals (enclosed in # tags - flexy
            * hack/convention).
            * - Variable binding: Look in output object scope first, then
            * template scope.
            */
            if (!$isLiteral) {
                $varVal             = str_replace('.','->',trim($value));
                $varVal             = '(isset($t->' . $varVal. ')) ? $t->' . $varVal .' : $'. $varVal;
            } else  {
                $varVal             = preg_replace('@\#(.*)\#@','"\1"',$varVal);
            }
                
            $aOutput[$varName]      = $varVal;
        }

        $varsOutput = "\n\$oOutput = clone \$t;\n";

        foreach ($aOutput as $key=>$val) {
            $varsOutput .= "\$oOutput->{$key} = {$val};\n";
        }
        
        
        
        /**
        * Pass code to compiler
        */
        return $this->compiler->appendPHP ( 
                "
                \$x = new HTML_Template_Flexy(\$this->options);
                \$x->compile('{$src}');
                {$varsOutput}
                \$x->outputObject(\$oOutput, \$this->elements);
                "
        );
    } 
    
    
    
    
}

 