<?php
// $Id: Heading.php,v 1.1 2004/06/06 15:44:34 pmjones Exp $


/**
* 
* This class implements a Text_Wiki_Parse to find source text marked to
* be a heading element, as defined by text on a line by itself prefixed
* with a number of plus signs (+). The heading text itself is left in
* the source, but is prefixed and suffixed with delimited tokens marking
* the start and end of the heading.
*
* @author Paul M. Jones <pmjones@ciaweb.net>
*
* @package Text_Wiki
*
*/

class Text_Wiki_Parse_Heading extends Text_Wiki_Parse {
	
	
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
	
	var $regex = '/^(\+{1,6}) (.*)/m';
	
	
	/**
	* 
	* Generates a replacement for the matched text.  Token options are:
	* 
	* 'type' => ['start'|'end'] The starting or ending point of the
	* heading text.  The text itself is left in the source.
	* 
	* @access public
	*
	* @param array &$matches The array of matches from parse().
	*
	* @return string A pair of delimited tokens to be used as a
	* placeholder in the source text surrounding the heading text.
	*
	*/
	
	function process(&$matches)
	{
		$start = $this->wiki->addToken(
			$this->rule, 
			array(
				'type' => 'start',
				'level' => strlen($matches[1]),
				'text' => $matches[2]
			)
		);
		
		$end = $this->wiki->addToken(
			$this->rule, 
			array(
				'type' => 'end',
				'level' => strlen($matches[1])
			)
		);
		
		return $start . $matches[2] . $end . "\n";
	}
}
?>