<?php
// $Id: Newline.php,v 1.1 2004/06/06 15:44:34 pmjones Exp $


/**
* 
* This class implements a Text_Wiki_Parse to mark implied line breaks in the
* source text, usually a single carriage return in the middle of a paragraph
* or block-quoted text.
*
* @author Paul M. Jones <pmjones@ciaweb.net>
*
* @package Text_Wiki
*
*/

class Text_Wiki_Parse_Newline extends Text_Wiki_Parse {
	
	
	/**
	* 
	* The regular expression used to parse the source text and find
	* matches conforming to this rule.  Used by the parse() method.
	* 
	* @access public
	* 
	* @var string
	* 
	* @see parse()
	* 
	*/
	
	var $regex = '/([^\n])\n([^\n])/m';
	
	
	/**
	* 
	* Generates a replacement token for the matched text.
	* 
	* @access public
	*
	* @param array &$matches The array of matches from parse().
	*
	* @return string A delimited token to be used as a placeholder in
	* the source text.
	*
	*/
	
	function process(&$matches)
	{	
		return $matches[1] .
			$this->wiki->addToken($this->rule) .
			$matches[2];
	}
}

?>