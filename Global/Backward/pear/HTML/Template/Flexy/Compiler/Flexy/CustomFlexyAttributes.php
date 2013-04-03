<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors:  Rick < reick at coalescentdesign dot com >                 |
// +----------------------------------------------------------------------+
//
/** Class called from HTML_Template_Flexy_Compiler_Flexy_Tag's toString() method.
*	For handling new custom flexy namespaced attributes. 
*/
class HTML_Template_Flexy_Compiler_Flexy_CustomFlexyAttributes  
{

    /**
    * doCustomAttributes 
    * - for every flexy namespaced attribute found in the element parameter, 
    *	if there is a method here to handle it then call the method.
    *
    * @params   object HTML_Template_Flexy_Token
    * @return   none 
    * @access   public 
    */
    function doCustomAttributes(&$element) 
    {
        
        foreach ($element->ucAttributes as $key=>$value) {
            
            list($namespace,$attribute) = (strpos($key, ":") > -1) ? explode(':',$key) : array("", $key);
            $method = strtolower($attribute) . 'Attribute';
            if (strtolower($namespace) != 'flexy') {
                continue;
            }
            if ((strlen($attribute)) && (method_exists($this,$method))) {
                $this->{$method}($element, $element->getAttribute($key));
            }
        }  
    }

   /**
    * flexy:content attribute handler
    *
    * Examples:
    * <anyTag... flexy:content="methodOrVariableOrNothing" .../>
    * <anyTag... flexy:content="methodOrVariableOrNothing" ...></anyTag>
    * <anyTag... flexy:content="methodOrVariableOrNothing" ...>All this <b>CONTENT</b> will be <i>replaced<i> by
    * the result of methodOrVariableOrNothing</anyTag>
    *
    * Replaces element content with the result of the variable or method call or empty string. 
    * Useful for replacing example content/html from the compiled template, while keeping it in the source 
    * template for viewing when not running php. The example content/html will be replaced by dynamic content at run-time.
    *
    * Substitute for <anyTag...>{methodOrVariable}</anyTag>
    * 
    * @params   object HTML_Template_Flexy_Token
    * @params   string attribute value
    * @return   none 
    * @access   private 
    */
    function contentAttribute(&$element,$val) 
    {
        // assign method or variable $val as the child token of this element, potentially replacing any existing children
        // default: special case if $val is empty - simply set children to null
        $element->children = null;
        if (! empty($val)) {
            $this->replaceChildren($element,$val);
        }  

        // do we have to add a seperate closing tag token to surround the content within...
        if ($element->close)  {
            return;
        }
        if ($element->getAttribute('/') !== false)  {
                // valid xhtml (eg. <tag />)
                // remove the '/' since we must add a seperate closing tag token to surround the content within
            unset($element->attributes['/']);
            unset($element->ucAttributes['/']);
        }  else {
            // FIXME: error not valid xhtml 
        }
        
        // add a seperate closing tag token to surround the content within
        $element->close = $element->factory("EndTag",array('/'.$element->oTag), $element->line);
    } 
    
    /**
    * flexy:replace attribute handler
    *
    * Examples:
    * <anyTag... flexy:replace="methodOrVariableOrNothing" .../>
    * <anyTag... flexy:replace="methodOrVariableOrNothing" ...></anyTag>
    * <anyTag... flexy:replace="methodOrVariableOrNothing" ...>Entire <b>element</b> including tag <i>replaced<i> by 
    * the result of methodOrVariableOrNothing</anyTag>
    *
    * Replaces entire element with the result of the variable or method call or empty string. 
    * Useful for removing example html from the compiled template, while keeping it in the source template for viewing
    * when not running php. The example html will be replaced by dynamic content at run-time.
    *
    * Substitute for {methodOrVariable}
    * 
    * @params   object HTML_Template_Flexy_Token
    * @params   string attribute value
    * @return   none 
    * @access   private 
    */
    function replaceAttribute(&$element,$val) 
    {
        // Setting tag to empty will prevent the opening and closing tags from beinging displayed
        $element->tag = null;
        $element->oTag = null;

        // assign method or variable $val as the child token of this element, potentially replacing any existing children
        // special case if $val is empty - simply set children to null
        $element->children = null;
        if (! empty($val)) {
            //echo '<br/>VAL IS: ' . $val;
            $this->replaceChildren($element,$val);
        }	
    }

    /**
    * flexy:omittag attribute handler
    * <... flexy:omittag ...>
    * <... flexy:omittag="" ...>
    * <... flexy:omittag="anything" ...>
    * Removes the tag but keeps the contents of the element including child elements. This is
    * useful for flexy:if and flexy:foreach when the tag isn't wanted but you would
    * prefer not to use {} placeholders for conditionals and loops.
    * 
    * @params   object HTML_Template_Flexy_Token
    * @params   string attribute value
    * @return   none 
    * @access   private 
    */
    function omittagAttribute(&$element,$val) 
    {
        // Setting tag to empty will prevent the opening and closing tags from beinging displayed
        $element->tag = null;
        $element->oTag = null;
    }

    /**
    * replaceChildren 
    * - replaces element children with the method or variable Token generated from the $val parameter
    * 
    * @params   object HTML_Template_Flexy_Token
    * @params   string attribute value
    * @return   none 
    * @access   private 
    */
    function replaceChildren(&$element,&$val)
    {
        // Most of the this method is borrowed from parseAttributeIf() in HTML_Template_Flexy_Compiler_Flexy_Tag
        
        // If this is a method, not a variable (last character is ')' )...
        if (substr($val,-1) == ')') {
            // grab args..
            $args = substr($val,strpos($val,'(')+1,-1);
            // simple explode ...
            
            $args = strlen(trim($args)) ? explode(',',$args) : array();
            //print_R($args);
            
            // this is nasty... - we need to check for quotes = eg. # at beg. & end..
            $args_clean = array();
            // clean all method arguments...
            for ($i=0; $i<count($args); $i++) {
                if ($args[$i]{0} != '#') {
                    $args_clean[] = $args[$i];
                    continue;
                }
                // single # - so , must be inside..
                if ((strlen($args[$i]) > 1) && ($args[$i]{strlen($args[$i])-1}=='#')) {
                    $args_clean[] = $args[$i];
                    continue;
                }
                
                $args[$i] .=',' . $args[$i+1];
                // remove args+1..
                array_splice($args,$i+1,1);
                $i--;
                // reparse..
            }
            
            //echo('<br/>VAL: ' . $val . ' is seen as method');
            
            $childToken =  $element->factory('Method',array(substr($val,0,strpos($val,'(')), $args_clean), $element->line);
        } else {

            //echo('<br/>VAL: ' . $val . ' is seen as var');
            $childToken =  $element->factory('Var', '{'.$val.'}', $element->line);
        }

        $element->children = array($childToken);
        
        // move flexy:if's End postfix of the start tag to the child token
        if (!$element->close && $element->postfix) {
            $element->children = array_merge($element->children, $element->postfix);
            $element->postfix = '';
        }


    }
}

 

