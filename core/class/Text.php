<?php
/**
 * Controls the text parsing and profanity controls for phpWebSite
 * Also contains extra HTML utilities
 * 
 * @version $Id$
 * @author  Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
 * @author  Adam Morton <adam@NOSPAM.tux.appstate.edu>
 * @author  Steven Levin <steven@NOSPAM.tux.appstate.edu>
 * @author  Don Seiler <don@NOSPAM.seiler.us>
 * @package Core
 */

class PHPWS_Text {

  /**
   * Removes profanity from a text string
   *
   * Profanity definitions are set by the core in the textSettings.php file
   *
   * @author Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
   * @param  string $text Text to be parsed
   * @return string Parsed text
   * @access public
   */
  function profanityFilter($text) {
    PHPWS_Core::configRequireOnce("core", "profanity.php");
    if (!is_string($text))
      return PHPWS_Error::get(PHPWS_TEXT_NOT_STRING, "core", "PHPWS_Text::profanityFilter");

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
      return PHPWS_Error::get(PHPWS_TEXT_NOT_STRING, "core", "PHPWS_Text::sentence");

    if (strstr($text, "\r"))
      $text_array = explode("\r\n",$text);
    else
      $text_array = explode("\n",$text);

    return $text_array;
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
    if (!is_string($text)) exit ("breaker() was not sent a string");

    $text_array = PHPWS_Text::sentence($text);
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

    $search_string = implode("|", $endings);

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
    return implode("", $content);
  }// END FUNC breaker()


  /**
   * Returns a string with backslashes before characters that need
   * to be quoted in database queries.
   *
   * Different than basic command as it checks to see if magic slashes is on.
   * If magic quotes is on, then addslashes will just return the string
   *
   * @author Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
   * @param  string $text  Text to addslashes to.
   * @return string Slashed text
   * @access public
   */
  function addslashes($text) {
    if (get_magic_quotes_gpc()) return $text;
    else return addslashes($text);
  }// END FUNC addslashes


  /**
   * Returns a string with backslashes removed BUT ONLY
   * if magic slashes is on.
   *
   * @author Matthew McNaney <matt@NOSPAM.tux.appstatee.edu>
   * @param  string $text Text to be stripped
   * @return string $text Stripped text
   * @access public
   */
  function stripslashes($text) {
    if (get_magic_quotes_gpc()==1) return stripslashes($text);
    else return $text;
  }// END FUNC stripslashes()

   /**
   * Removes tags from text
   *
   * This function replaces the functionality of the 'parse' function
   * Should be used after a post or get or before saving it to the database
   *
   * @author                       Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
   * @param   string  text         Text to parse
   * @param   mixed   allowedTags  The tags that will not be stripped from the text
   * @return  string  text         Stripped text
   */
  function parseInput($text, $allowedTags=NULL){
    $text = PHPWS_Text::stripSlashQuotes($text);

    if ($allowedTags == "none")
      $allowedTagString = NULL;
    elseif (is_array($allowedTags))
      $allowedTagString = implode("", $allowedTags);
    elseif (is_string($allowedTags))
      $allowedTagString = $allowedTags;
    else
      $allowedTagString = PHPWS_ALLOWED_TAGS;

    $replace_source = array("/(\[code\])(.*)(\[\/code\])/seU",
			    "/<br>/",
			    "/'/");
    $replace_dest = array("'\\1' . str_replace('\n', '', PHPWS_Text::utfEncode('\\2')) . '\\3'",
			  "<br />",
			  "&#39;");

    $text = preg_replace($replace_source, $replace_dest, $text);

    return strip_tags($text, $allowedTagString);
  }

  function utfEncode($text){
    $text = PHPWS_Text::stripSlashQuotes($text);

    $search = array("/</", "/>/");
    $replace = array('&#x003c;', '&#x003e;');

    return preg_replace($search, $replace, $text);
  }

  /**
   * Prepares text for display
   *
   * This function replaces the functionality of the 'parse' and appends
   * the breaker function.
   * Should be used after retrieving data from the database
   *
   * @author                       Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
   * @param   string  text         Text to parse
   * @return  string  text         Stripped text
   */
  function parseOutput($text, $printTags=FALSE){
    require_once("HTML/BBCodeParser.php");

    // Set up BBCodeParser
    $config = parse_ini_file(PHPWS_SOURCE_DIR . "/conf/BBCodeParser.ini", true);
    $options = &PEAR::getStaticProperty("HTML_BBCodeParser", "_options");
    $options = $config["HTML_BBCodeParser"];
    unset($options);

    if (FILTER_PROFANITY) 
      $text = PHPWS_Text::profanityFilter($text);

    if ($printTags)
      $text = htmlspecialchars($text);

    $text = preg_replace("/&(?!\w+;)(?!#)/U", "&#x0026;\\1", $text);

    $text = str_replace("\$", '&#x0024;', $text);

    // Parse BBCode
    $parser = new HTML_BBCodeParser();
    $parser->setText($text);
    $parser->parse();
    return PHPWS_Text::breaker($parser->getParsed());
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
   * @author Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
   * @param  string  $userEntry Text to be checked
   * @param  string  $type      What type of comparison
   * @return boolean TRUE on valid input, FALSE on invalid input
   * @access public
   */
  function isValidInput($userEntry, $type=NULL) {
    if (empty($userEntry) || !is_string($userEntry)) return FALSE;

    switch ($type) {
    case "chars_space":
    if (eregi("^[a-z_0-9 ]+$",$userEntry)) return TRUE;
    else return FALSE;
    break;

    case "number":
    if (ereg("^[0-9]+$",$userEntry)) return TRUE;
    else return FALSE;
    break;

    case "url":
    if (eregi("^(http:\/\/)[_a-z0-9-]+(\.[_a-z0-9-]+|\/)", $userEntry)) return TRUE;
    else return FALSE;
    break;

    case "email":
    if (eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)+$", $userEntry)) return TRUE;
    else return FALSE;
    break;

    case "file":
    if (eregi("^[a-z_0-9\.]+$",$userEntry)) return TRUE;
    else return FALSE;
    break;

    default:
      if (eregi("^[a-z_0-9]+$",$userEntry)) return TRUE;
      else return FALSE;
    break;
    }
  }// END FUNC validForm()


  /**
   * Allows a quick link function for phpWebSite modules to the index.php.
   * 
   * A replacement for the clunky function link. This is for modules accessing
   * local information ONLY. It adds the hub web address and index.php automatically.
   * You supply the name of the module and the variables.
   *
   * @author Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
   * @param string title String to appear as the 'click on' word(s)
   * @param string module Name of module to access
   * @param array getVars Associative array of GET variable to append to the link
   * @return string The complated link.
   */
  function moduleLink($title, $module=NULL, $getVars=NULL, $target=NULL){
    $link[] = "<a href=\"./";
    $link[] = "index.php";

    $link[] = "?";
    
    $vars[] = "module=$module";
    if (is_array($getVars)){
      foreach ($getVars as $var_name=>$value)
	$vars[] = $var_name . "=" . $value;
    }
    
    $link[] = implode("&amp;", $vars);

    $link[] = "\"";

    if ($target=="blank" || $target === TRUE)
      $link[] = " target=\"_blank\" ";
    elseif ($target=="index")
      $link[] = " target=\"index\" ";

    $link[] = ">";

    return implode("", $link) . strip_tags($title, "<img>") . "</a>";
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
  function checkLink($link){
    if (!stristr($link, "://")) return "http://".$link;
    else return $link;
  }// END FUNC checkLink()
  

  /**
   * Returns TRUE if the text appears to have unslashed quotes or apostrophes
   *
   * @author Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
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
   * @author Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
   * @param  string $text Text to remove slashes from
   * @return string $text Parsed text
   * @access public
   */
  function stripSlashQuotes($text){
    $text = str_replace("\'", "'", $text);
    $text = str_replace("\\\"", "\"", $text);
    return $text;
  }// END FUNC stripSlashQuotes()
}//END CLASS CLS_text

?>
