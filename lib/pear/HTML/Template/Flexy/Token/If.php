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
// | Authors:  Alan Knowles <alan@akbkhome>                               |
// +----------------------------------------------------------------------+
//
// $Id: If.php,v 1.5 2003/12/10 02:36:26 alan_k Exp $
//
 
/**
* Class to handle If statements
*
*
*/
class HTML_Template_Flexy_Token_If extends HTML_Template_Flexy_Token{ 
    /**
    * Condition for the if statement.
    * @var string // a variable
    * @access public
    */
    
    var $condition;
     /**
    * if the statement is negative = eg. !somevar..
    * @var string
    * @access public
    */
    
    
    var $isNegative = '';
    
    /**
    * Setvalue - a string
    * @see parent::setValue()
    */
    function setValue($value) {
        //var_dump($value);
        if (strlen($value) && $value{0} == '!') {
            $this->isNegative = '!';
            $value = substr($value,1);
        }
        $this->condition=$value;
    }
    

}

 
 
  