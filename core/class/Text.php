<?php
/**
 * Controls the text parsing and profanity controls for phpWebSite
 * Also contains extra HTML utilities
 * 
 * @version $Id$
 * @author  Matthew McNaney <matt at tux dot appstate dot edu>
 * @author  Adam Morton <adam@NOSPAM.tux.appstate.edu>
 * @author  Steven Levin <steven@NOSPAM.tux.appstate.edu>
 * @author  Don Seiler <don@NOSPAM.seiler.us>
 * @package Core
 */

PHPWS_Core::configRequireOnce('core', 'profanity.php');

class PHPWS_Text {

  /**
   * Removes profanity from a text string
   *
   * Profanity definitions are set by the core in the textSettings.php file
   *
   * @author Matthew McNaney <matt at tux dot appstate dot edu>
   * @param  string $text Text to be parsed
   * @return string Parsed text
   * @access public
   */
  function profanityFilter($text) {
    PHPWS_Core::configRequireOnce('core', 'profanity.php');
    if (!is_string($text))
      return PHPWS_Error::get(PHPWS_TEXT_NOT_STRING, 'core', 'PHPWS_Text::profanityFilter');

    $words = unserialize(PROFANE_WORDS);

    foreach ($words as $matchWord=>$replaceWith)
      $text = preg_replace("/$matchWord/i", $replaceWith, $text);

    return $text;
  }// END FUNC profanityFilter()


  /**
   * Breaks text up into row sentences in an array
   * 
   * @author Matt McNaney <matt@NOSPAM_tux.appstate.edu>
   * @param  string  $text       Text string to break into array
   * @return array   $text_array Array of sentences
   * @access public
   */
  function sentence($text, $stripNewlines = FALSE){
    if (!is_string($text))
      return PHPWS_Error::get(PHPWS_TEXT_NOT_STRING, 'core', 'PHPWS_Text::sentence');

    return preg_split("/\r\n|\n/", $text);
  }// END FUNC sentence()


  /**
   * Adds breaks to text where newlines exist
   * 
   * This function will ONLY add a break if the current break is not preceded
   * by certain tags (see below). This will prevent breaks in tables etc.
   * Make sure you enter the tags in regular expression form.
   *
   * @author Matt McNaney <matt@NOSPAM_tux.appstate.edu>
   * @param  string $text    Text you wish breaked
   * @return string $content Formatted text
   * @access public
   */
  function breaker($text){
    if (!is_string($text)) exit ('breaker() was not sent a string');

    $text_array = PHPWS_Text::sentence(trim($text));

    if (count($text_array) == 1)
      return $text . "\n";

    $lines = count($text_array);
    $endings = array ("<br \/>",
		      "<br>",
		      "<img .*>",
		      "<\/?p.*>",
		      "<\/?area.*>",
		      "<\/?map.*>",
		      "<\/?li.*>",
		      "<\/?ol.*>",
		      "<\/?ul.*>",
		      "<\/?dl.*>",
		      "<\/?dt.*>",
		      "<\/?dd.*>",
		      "<\/?table.*>",
		      "<\/?th.*>",
		      "<\/?tr.*>",
		      "<\/?td.*>",
		      "<\/?h..*>");

    $search_string = implode('|', $endings);

    $count = 0;
    $content = NULL;
    $preFlag = FALSE;

    foreach ($text_array as $sentence){
      if(!$preFlag) {
	if(preg_match("/<pre>\$/iU", trim($sentence))) {
	  $preFlag = TRUE;
	  $content[] = $sentence."\n";
	  continue;
	}
	if (!preg_match("/($search_string)$/iU" , trim($sentence))) $content[] = $sentence."<br />\n";
	else $content[] = $sentence."\n";
      } else if(preg_match("/<\/pre>\$/iU", trim($sentence))) {
	$preFlag = FALSE;
	$content[] = $sentence."\n";
	continue;
      } else {
	$content[] = $sentence."\n";
      }
    }

    return implode('', $content);
  }// END FUNC breaker()


   /**
   * Removes tags from text
   *
   * This function replaces the functionality of the 'parse' function
   * Should be used after a post or get or before saving it to the database
   *
   * @author                       Matthew McNaney <matt at tux dot appstate dot edu>
   * @param   string  text         Text to parse
   * @param   mixed   allowedTags  The tags that will not be stripped from the text
   * @return  string  text         Stripped text
   */
  function parseInput($text, $allowedTags=NULL){
    if ($allowedTags == 'none')
      $allowedTagString = NULL;
    elseif (is_array($allowedTags))
      $allowedTagString = implode('', $allowedTags);
    elseif (is_string($allowedTags))
      $allowedTagString = $allowedTags;
    else
      $allowedTagString = PHPWS_ALLOWED_TAGS;

    $text = strip_tags($text, $allowedTagString);

    PHPWS_Text::encodeXHTML($text);
    if (MAKE_ADDRESSES_RELATIVE) {
      PHPWS_Text::makeRelative($text);
    }
    return trim($text);
  }

  function XHTMLArray(){
    $xhtml["\$"] = '&#x0024;';
    $xhtml['<br>'] = '<br />';
    
    return $xhtml;
  }

  function encodeXHTML(&$text){
    $xhtml = PHPWS_Text::XHTMLArray();
    $text = strtr($text, $xhtml);
    $text = preg_replace("/&(?!\w+;)(?!#)/U", "&amp;\\1", $text);
    $text = htmlentities($text, ENT_QUOTES);
    return $text;
  }

  function textareaDecode($text){
    $text = str_replace('<br />', '', $text);
    return $text;
  }

  /**
   * Prepares text for display
   *
   * This function replaces the functionality of the 'parse' and appends
   * the breaker function.
   * Should be used after retrieving data from the database
   *
   * @author                       Matthew McNaney <matt at tux dot appstate dot edu>
   * @param   string  text         Text to parse
   * @return  string  text         Stripped text
   */
  function parseOutput($text, $printTags = FALSE, $filter_profanity = TRUE, $run_breaker = TRUE)
  {
    if (empty($text))
      return NULL;
    require_once('HTML/BBCodeParser.php');

    // Set up BBCodeParser
    $config  = parse_ini_file(PHPWS_SOURCE_DIR . '/config/core/BBCodeParser.ini', true);
    $options = &PEAR::getStaticProperty('HTML_BBCodeParser', '_options');
    $options = $config['HTML_BBCodeParser'];
    unset($options);

    if ($filter_profanity && FILTER_PROFANITY)
      $text = PHPWS_Text::profanityFilter($text);

    // Parse BBCode
    $parser = new HTML_BBCodeParser();
    $parser->setText($text);
    $parser->parse();
    $text = $parser->getParsed();
    if ($run_breaker) {
      $text = PHPWS_Text::breaker($text);
    }

    if ($printTags == FALSE)
      $text = html_entity_decode($text, ENT_QUOTES);

    return $text;
  }

  /**
   * Checks the validity of text based on the type
   *
   * Designed to be a catch all method to parse critical text.
   * - chars_space : input must be alphanumberic. Spaces allowed
   * - number : input must be numeric
   * - email : input must appear to be valid email address
   * - file : input must appear to be a proper file name format
   * - default : alphanumeric and underline ONLY
   *
   * Should be used anytime user input directly affects program logic,
   * is used to pull database data, etc. Also, will ALWAYS return FALSE
   * if it receives blank data. 
   *
   * @author Matthew McNaney <matt at tux dot appstate dot edu>
   * @param  string  $userEntry Text to be checked
   * @param  string  $type      What type of comparison
   * @return boolean TRUE on valid input, FALSE on invalid input
   * @access public
   */
  function isValidInput($userEntry, $type=NULL) {
    if (empty($userEntry) || !is_string($userEntry)) return FALSE;

    switch ($type) {
    case 'chars_space':
    if (preg_match("/^[\w\s]+$/i",$userEntry)) return TRUE;
    else return FALSE;
    break;

    case 'number':
    if (preg_match("/^[\d]+$/",$userEntry)) return TRUE;
    else return FALSE;
    break;

    case 'url':
    if (preg_match("/^(http(s){0,1}:\/\/)[_a-z0-9-]+(\.[_a-z0-9-]+|\/)/i", $userEntry)) return TRUE;
    else return FALSE;
    break;

    case 'email':
    if (preg_match("/^[\w]+(\.[\w]+)*@[\w]+(\.[\w]+)+$/i", $userEntry)) return TRUE;
    else return FALSE;
    break;

    case 'file':
    if (preg_match("/^[\w\.]+$/i",$userEntry)) return TRUE;
    else return FALSE;
    break;

    default:
      if (preg_match("/^[\w]+$/i",$userEntry)) return TRUE;
      else return FALSE;
    break;
    }
  }// END FUNC isValidInput()

  /**
   * Returns a reroutable link
   *
   * MOD_REWRITE_ENABLED must be true and mod_reroute must be enabled
   * in Apache
   */
  function rerouteLink($subject, $module, $action, $id=NULL){
    if ((bool)MOD_REWRITE_ENABLED == FALSE) {
      return PHPWS_Text::moduleLink($subject, $module, array('action' => $action, 'id' => $id));
    } else {
      if (!isset($id)) {
	return "<a href=\"{$module}/{$action}\">$subject</a>";
      } else {
	return "<a href=\"{$module}/{$action}/{$id}\">$subject</a>";
      }
    }
  }


  function secureLink($subject, $module=NULL, $getVars=NULL, $target=NULL, $title=NULL){
    if (Current_User::isLogged()) {
      $getVars['authkey'] = Current_User::getAuthKey();
    }

    return PHPWS_Text::moduleLink($subject, $module, $getVars, $target, $title);
  }

  /**
   * Allows a quick link function for phpWebSite modules to the index.php.
   * 
   * A replacement for the clunky function link. This is for modules accessing
   * local information ONLY. It adds the hub web address and index.php automatically.
   * You supply the name of the module and the variables.
   *
   * @author Matthew McNaney <matt at tux dot appstate dot edu>
   * @param string title String to appear as the 'click on' word(s)
   * @param string module Name of module to access
   * @param array getVars Associative array of GET variable to append to the link
   * @return string The complated link.
   */
  function moduleLink($subject, $module=NULL, $getVars=NULL, $target=NULL, $title=NULL){
    $link[] = '<a ';

    if (isset($title))
      $link[] = 'title="' . strip_tags($title) . '" ';

    $link[] = 'href="./';

    $link[] = 'index.php';

    if (isset($module)){
      $link[] = '?';
      $vars[] = "module=$module";
    }

    if (is_array($getVars)){
      foreach ($getVars as $var_name=>$value)
	$vars[] = $var_name . '=' . $value;
    }

    if (isset($vars))
      $link[] = implode('&amp;', $vars);

    $link[] = '"';

    if ($target=='blank' || $target === TRUE)
      $link[] = ' target="_blank" ';
    elseif ($target=="index")
      $link[] = " target=\"index\" ";

    $link[] = ">";

    return implode("", $link) . strip_tags($subject, "<img>") . "</a>";
  }// END FUNC indexLink()


  /**
   * Appends http:// if missing
   *
   * More text detail
   *
   * @author Matthew McNaney <matt at tux dot appstate dot edu>
   * @param  string $link Link string to check
   * @return string $link Appended string
   * @access public
   */
  function checkLink($link, $ssl=FALSE){
    if (!stristr($link, "://")){
      if ($ssl)
	return "https://".$link;
      else
	return "http://".$link;
    }
    else return $link;
  }// END FUNC checkLink()
  

  /**
   * Returns TRUE if the text appears to have unslashed quotes or apostrophes
   *
   * @author Matthew McNaney <matt at tux dot appstate dot edu>
   * @param  string  $text Text to be checked for unslashed quotes or apostrophes
   * @return boolean TRUE on success, FALSE on failure
   * @access public
   */
  function checkUnslashed($text){
    if (preg_match("/[^\\\]+[\"']/", $text))
      return TRUE;
    else
      return FALSE;
  }// END FUNC checkUnslashed()

  /**
   * Removes slashes ONLY from quotes or apostrophes, nothing else
   *
   * @author Matthew McNaney <matt at tux dot appstate dot edu>
   * @param  string $text Text to remove slashes from
   * @return string $text Parsed text
   * @access public
   */
  function stripSlashQuotes($text){
    $text = str_replace("\'", "'", $text);
    $text = str_replace("\\\"", "\"", $text);
    return $text;
  }// END FUNC stripSlashQuotes()

  function makeRelative(&$text){
    $address = addslashes(PHPWS_Core::getHomeHttp());
    $text = str_replace($address, "./", $text);
  }

}//END CLASS CLS_text

?>
