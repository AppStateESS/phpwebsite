<?php
/**
 * Controls the text parsing and profanity controls for phpWebSite
 * Also contains extra HTML utilities
 * 
 * @version $Id$
 * @author  Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
 * @author  Adam Morton <adam@NOSPAM.tux.appstate.edu>
 * @author  Steven Levin <steven@NOSPAM.tux.appstate.edu>
 * @package Core
 */
class PHPWS_Text {

  /**
   * An array of "bad" words for this site.  Used to filter
   * profanity.
   * @var array
   * @access private
   */
  var $bad_words;

  /**
   * Determines whether or not to strip profanity.
   * @var boolean
   * @access private
   */
  var $strip_profanity;

  /**
   * An array of allowed HTML tags for the site.
   * @var array
   * @access private
   */
  var $allowed_tags;

  /**
   * Text settings for the core
   *
   * Only loaded if a database connection is successful
   *
   * @author Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
   */
  function loadTextSettings(){

    if (file_exists($this->home_dir . "conf/textSettings.php"))
      $textFile = $this->home_dir . "conf/textSettings.php";
    elseif(file_exists($this->source_dir . "conf/textSettings.php"))
      $textFile = $this->source_dir . "conf/textSettings.php";
    else {
      exit("Error: Unable to locate textSettings file.<br />" . $this->source_dir . "conf/textSettings.php");
      return;
    }

    include ($textFile);
    $this->allowed_tags = $allowed_tags;
    $this->bad_words = $bad_words;
    $this->strip_profanity = $strip_profanity;
  }

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
    if (!is_array($GLOBALS["core"]->bad_words))
      exit("Error: bad_words variable in your textSettings file is not an array");

    foreach ($GLOBALS["core"]->bad_words as $matchWord=>$replaceWith)
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
    if (!is_string($text)) exit ("sentence() was not sent a string");

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

    $loop = 0;
    $search_string = NULL;
    foreach ($endings as $tag){
      if ($loop) $search_string .= "|";
      $search_string .= $tag."\$";
      $loop = 1;
    }

    $count = 0;
    $content = NULL;
    foreach ($text_array as $sentence){
      $count++;
      if ($count < $lines){
	if (!preg_match("/".$search_string."/iU" , trim($sentence))) $content .= $sentence."<br />\n";
	else $content .= $sentence."\n";
      } else 
	$content .= $sentence;
    }
    return $content;
  }// END FUNC breaker()


  /**
   * Returns true if the $char passed is an alphabetic character and false
   * if it is not.
   *
   * @author Adam Morton <adam@NOSPAM.tux.appstate.edu>
   * @param  string  $char The character to be tested
   * @return boolean TRUE if it's in the english alphabet, FALSE if not
   * @access public
   */
  function isAlpha ($char) {
    return !preg_match("/[^a-zA-Z]/", $char);
  }// END FUNC isAlpha()


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
   * An alias for the stripslashes function.
   *
   * Helps to distinguish it between the PHPWS_Text function and the PHP
   * function and how the handle slashes.
   *
   * @param  string $text Text to be stripped
   * @return string $text Stripped text
   * @access public
   */
  function magicstrip($text) {
    return PHPWS_Text::stripslashes($text);
  }

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
    $text = PHPWS_Text::magicstrip($text);

    if ($allowedTags == "none")
      $allowedTagString = NULL;
    elseif (is_array($allowedTags))
      $allowedTagString = implode("", $allowedTags);
    elseif (is_string($allowedTags))
      $allowedTagString = $allowedTags;
    else {
      $allowedTagString = PHPWS_ALLOWED_TAGS;
    }

    $text = preg_replace("/(\[code\])(.*)(\[\/code\])/seU", "'\\1' . stripslashes(PHPWS_Core::utfEncode('\\2')) . '\\3'", $text);

    return strip_tags($text, $allowedTagString);
  }

  function utfEncode($text){
    $search = array("/</", "/>/", "/&([^#])/");
    $replace = array('&#x003c;', '&#x003e;', '&#x0026;');

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
    /*
    if ($GLOBALS["core"]->strip_profanity) 
      $text = $GLOBALS["core"]->profanityFilter($text);
    */

    if ($printTags)
      $text = htmlspecialchars($text);

    $text = preg_replace("/&([^#\w])/", "&#x0026;\\1", $text);
    $text = preg_replace("/\[code\](.*)\[\/code\]/seU", "'<pre>'.stripslashes('\\1') . '</pre>'", $text);
    $text = str_replace("\$", '&#x0024;', $text);
    return PHPWS_Text::breaker($text);
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
    case "chars_space":
      return preg_match("/^[a-z_0-9\s]+$/i",$userEntry);
    break;

    case "number":
      return preg_match("/^[0-9]+$/",$userEntry);
    break;

    case "email":
      return preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)+$/i", $userEntry);
    break;

    case "file":
      return preg_match("/^[a-z_0-9\.]+$/i",$userEntry);
    break;

    default:
      return preg_match("/^[a-z_0-9]+$/i",$userEntry);
    break;
    }
  }// END FUNC validForm()


  /**
   * Returns an image string
   *
   * If width or height are not supplied, the function supplies them for you.
   * This function will attempt to return an empty box if it cannot find the file.
   *
   * @author Matthew McNaney <matt at tux dot appstate dot edu>
   * @param  string  $address The address of the image
   * @param  string  $alt     The alt text for the image (required)
   * @param  mixed   $width   Width of the graphic
   * @param  mixed   $height  Height of the graphic
   * @param  mixed   $border  Width of the graphics border
   * @param  boolean $blank   Unknown
   * @return string  $image   The html image tag
   * @access public
   */
  function imageTag($address, $alt=NULL, $width=NULL, $height=NULL, $border=0, $blank=FALSE){
    $dimensions = NULL;

    if ($GLOBALS['core']->isHub){
      $checkDir = str_replace(PHPWS_SOURCE_HTTP, PHPWS_SOURCE_DIR, $address);
      $address = str_replace("http://", "", $address);
      $address = str_replace(PHPWS_SOURCE_HTTP, "", $address);
    }
    else {
      if (stristr($address, PHPWS_HOME_HTTP . "images/")) {
      $checkDir = str_replace(PHPWS_HOME_HTTP, PHPWS_HOME_DIR, $address);
      $address = str_replace("http://", "", $address);
      $address = str_replace(PHPWS_HOME_HTTP, "", $address);
      }
      else {
	$checkDir = str_replace(PHPWS_SOURCE_HTTP, PHPWS_SOURCE_DIR, $address);
	$address = PHPWS_Text::checkLink($address);
      }
    }

    if (is_null($width) && is_null($height)){
      $size = @getimagesize($checkDir);
      if ($size == FALSE)
	return NULL;
      else
	$dimensions = " " . $size[3];
    } else {
      if (isset($width))
	$dimensions .= " width=\"$width\"";
      if (isset($height))
	$dimensions .= " height=\"$height\"";
    }

    $border = " border=\"$border\"";
    $alt = " alt=\"" . strip_tags($alt) . "\"";
    $image = "<img src=\"$address\"" . $dimensions . $border . $alt . " />";

    return $image;
  }// END FUNC imageTag()

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
  function moduleLink($title, $module=NULL, $getVars=NULL, $target=NULL){
    $link[] = "<a href=\"./";

    if (isset($module)){
      $link[] = "index.php?module=$module";

      if (is_array($getVars)){
	foreach ($getVars as $var_name=>$value){
	  $link[] = "&amp;";
	  
	  $link[] = $var_name . "=" . $value;
	  $i = 1;
	}
      }
    }

    if ($target=="blank" || $target === TRUE)
      $linkTarget = " target=\"_blank\" ";
    elseif ($target=="index")
      $linkTarget = " target=\"index\" ";
    else
      $linkTarget = NULL;

    $link[] = "\"" .$linkTarget . ">";
    $link[] = strip_tags($title, "<img>");
    $link[] = "</a>";

    return implode("", $link);
  }// END FUNC indexLink()

  
  /**
   * Returns a HREF link string
   *
   * Sending an array to $get_var will create a get suffix on
   * to the link. For example:
   *
   * $array["article_number"] = "5";
   * $array["preference"] = "all";
   *
   * $this->link("index.php", "Show All 5 Articles", "local", $array);
   * //Returns: <a href="your_web_site.com/index.php?article_number=5&preference=all">Show All 5 Articles</a>
   *
   * Finally if you use "index" or "blank" for $target, your link will open in 
   * a new window. Blank opens a new window each time. Index opens one new window
   * and any new links will open in that window.
   *
   * @author Matthew McNaney <matt at tux dot appstate dot edu>
   * @param  string   $address Where the link will go
   * @param  string   $text    Clickable text (or picture) to go to the address
   * @param  resource $get_var Associative array of get values to add to address
   * @param  string   $target  'blank' or 'index' to open a new window
   * @param  string   $onclick Command to execute upon clicking the link
   * @return string   $link    The link string
   * @access public
   */
  function link($address, $text, $getVar=NULL, $target=NULL, $onclick=NULL){
    $link[] = "<a href=\"$address";

    if (isset($getVar) && is_array($getVar)){
      $link[] = "?";
      foreach ($getVar as $var_name=>$value)
        $varList[] = $var_name . "=" . $value;

      $link[] = implode("&amp;", $varList);
    }

    $link[] = "\"";

    if ($onclick) $link[] = " onclick=\"$onclick\"";

    if ($target == "blank") $link[] = " target=\"_blank\"";
    elseif ($target == "index") $link[] = " target=\"index\"";

    if (isset($onclick)) $link[] = " onClick=\"$onclick\"";

    $link[] = ">$text</a>";

    return implode("", $link);
  }// END FUNC link()


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
    if (!stristr($link, "http://")) return "http://".$link;
    else return $link;
  }// END FUNC checkLink()
  


  /**
   * Removes spaces from a string
   *
   * If "replace" is sent, that character will replace the space.
   * Could be useful for filenames
   *
   * @author Matthew McNaney <matt at tux dot appstate dot edu>
   * @param  string $text    String to remove spaces from
   * @param  string $replace String to use instead of spaces
   * @return string The original string without spaces
   * @access public
   */
  function stripSpaces($text, $replace=NULL) {
    if (is_string($replace)) return str_replace(" ", substr($replace, 0, 1), $text);
    else return str_replace(" ", "", $text);
  }// END FUNC stripSpaces()


  /**
   * alphaNum
   *
   * Removes any character that is not alphanumeric
   *
   * @author Matthew McNaney
   * @param  string $stripper The string to strip non-alphanumeric characters from.
   * @return string The original string with all non-alphanumeric characters removed.
   * @access public
   */
  function alphaNum($stripper) {
    return preg_replace("/[^a-zA-Z0-9]/", "", $stripper);
  }// END FUNC alphaNum()


  /**
   * Returns TRUE if the text appears to have unslashed quotes or apostrophes
   *
   * @author Matthew McNaney <matt at tux dot appstate dot edu>
   * @param  string  $text Text to be checked for unslashed quotes or apostrophes
   * @return boolean TRUE on success, FALSE on failure
   * @access public
   */
  function checkUnslashed($text){
    if (preg_match("/[\s\w]+[\"']/", $text))
      return TRUE;
    else
      return FALSE;
  }// END FUNC checkUnslashed()

  /**
   * Removes quotes from a string
   *
   * @author Matthew McNaney <matt at tux dot appstate dot edu>
   * @param  string $text Text to remove quotes from
   * @return string $text Parsed text
   * @access public
   */
  function stripQuotes($text) {
    $text = str_replace("'", "", $text);
    $text = str_replace("\"", "", $text);
    return $text;
  }// END FUNC stripQuotes()

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
}//END CLASS CLS_text

?>
