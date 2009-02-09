<?php
  /**
   * Controls the text parsing and profanity controls for phpWebSite
   * Also contains extra HTML utilities
   *
   * See config/core/text_settings.php for configuration options
   *
   * @version $Id$
   * @author  Matthew McNaney <matt at tux dot appstate dot edu>
   * @author  Adam Morton
   * @author  Steven Levin
   * @author  Don Seiler <don@NOSPAM.seiler.us>
   * @package Core
   */

if (!defined('UTF8_MODE')) {
    define ('UTF8_MODE', false);
}

PHPWS_Core::requireConfig('core', 'text_settings.php');
PHPWS_Core::initCoreClass('Link.php');

if (!defined('PHPWS_HOME_HTTP')) {
    define('PHPWS_HOME_HTTP', './');
}

if (!defined('ALLOW_TEXT_FILTERS')) {
    define('ALLOW_TEXT_FILTERS', true);
}

if (!defined('ENCODE_PARSED_TEXT')) {
    define('ENCODE_PARSED_TEXT', true);
}

if (!defined('TEXT_FILTERS')) {
    define('TEXT_FILTERS', 'pear');
}

if (!defined('USE_BREAKER')) {
    define('USE_BREAKER', true);
}

if (!defined('ALLOW_SCRIPT_TAGS')) {
    define('ALLOW_SCRIPT_TAGS', false);
}

/**
 * Changing this to FALSE will _ALLOW SCRIPT TAGS_!
 * Change at your own risk.
 */
if (!defined('USE_STRIP_TAGS')) {
    define('USE_STRIP_TAGS', true);
}

class PHPWS_Text {
    public $use_profanity  = ALLOW_PROFANITY;
    public $use_breaker    = USE_BREAKER;
    public $use_strip_tags = USE_STRIP_TAGS;
    public $use_filters    = false;
    public $fix_anchors    = FIX_ANCHORS;
    public $collapse_urls  = COLLAPSE_URLS;
    public $allowed_tags  = null;


    public function __construct($text=null, $encoded=false)
    {
        $this->resetAllowedTags();
        $this->setText($text, $encoded);
    }

    public function decodeText($text)
    {
        return html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    }

    public function useFilters($filter)
    {
        $this->use_filters = (bool)$filter;
    }

    public function setText($text, $decode=ENCODE_PARSED_TEXT)
    {
        if (empty($text) || !is_string($text)) {
            return;
        }

        if ($decode) {
            $this->text = $this->decodeText($text);
        } else {
            $this->text = $text;
        }
    }

    public function breakPost($name)
    {
        $check_name = sprintf('%s_breaker', $name);
        return isset($_POST[$check_name]);
    }

    public function useProfanity($use = true)
    {
        $this->use_profanity = (bool)$use;
    }

    public function useBreaker($use = true)
    {
        $this->use_breaker = (bool)$use;
    }

    public function useStripTags($use = true)
    {
        $this->use_strip_tags = (bool)$use;
    }

    public function addAllowedTags($tags)
    {
        if (is_array($tags)) {
            $this->allowed_tags .= implode('', $tags);
        } else {
            $this->allowed_tags .= $tags;
        }
    }

    public function setAllowedTags($tags)
    {
        if (is_array($tags)) {
            $this->allowed_tags = implode('', $tags);
        } else {
            $this->allowed_tags = $tags;
        }
    }

    public function resetAllowedTags()
    {
        static $default_tags = null;

        if (empty($default_tags)) {
            $default_tags = preg_replace('/\s/', '',  strtolower(PHPWS_ALLOWED_TAGS));

            if (PHPWS_Core::allowScriptTags()) {
                $default_tags .= '<script>';
            } else {
                $default_tags = str_replace('<script>', '', $default_tags);
            }
        }

        $this->allowed_tags = $default_tags;
    }

    public function clearAllowedTags()
    {
        $this->allowed_tags = null;
    }

    public function getPrint()
    {
        if (empty($this->text)) {
            return null;
        }

        $text = $this->text;
        if (ALLOW_TEXT_FILTERS && $this->use_filters) {
            $text = PHPWS_Text::filterText($text);
        }

        if (!$this->use_profanity) {
            $text = PHPWS_Text::profanityFilter($text);
        }

        if ($this->use_strip_tags) {
            $text = strip_tags($text, $this->allowed_tags);
        }

        if ($this->use_breaker) {
            $text = PHPWS_Text::breaker($text);
        }

        if ($this->fix_anchors) {
            $text = PHPWS_Text::fixAnchors($text);
        }

        if ($this->collapse_urls) {
            $text = PHPWS_Text::collapseUrls($text);
        }

        return $text;
    }

    public function filterText($text)
    {
        static $filters = null;

        if (empty($filters)) {
            $fltr_list = explode(',', TEXT_FILTERS);
            foreach ($fltr_list as $fltr) {
                $dir = sprintf('%score/class/filters/%s.php', PHPWS_SOURCE_DIR, trim($fltr));

                if (is_file($dir)) {
                    require_once $dir;
                    $function_name = $fltr . '_filter';
                    if (function_exists($function_name)) {
                        $filters[] = $function_name;
                    }
                }
            }
            if (empty($filters)) {
                $filters = 1;
            }
        }

        // None of the filters worked/found
        if ($filters == 1) {
            return $text;
        }

        foreach ($filters as $filter) {
            $text = $filter($text);
        }
        return $text;
    }

    /**
     * Fixes plain anchors. Makes them relative to the current page.
     */
    public function fixAnchors($text)
    {
        $home_http = PHPWS_Core::getCurrentUrl();

        return preg_replace('/href="#(\w+)"/',
                            sprintf('href="%s#\\1"', $home_http),
                            $text);
    }

    /**
     * Mostly used to clean up windows high ascii characters
     */
    public function encodeXHTML($text)
    {
        $xhtml['™']    = '&trade;';
        $xhtml['•']    = '&bull;';
        $xhtml['°']    = '&deg;';
        $xhtml['©']    = '&copy;';
        $xhtml['®']    = '&reg;';
        $xhtml['…']    = '&hellip;';

        $xhtml['\$']   = '&#x24;';
        $xhtml['<br>'] = '<br />';

        $xhtml[chr(226).chr(128).chr(153)] = '&rsquo;';
        $xhtml[chr(226).chr(128).chr(156)] = '&ldquo;';
        $xhtml[chr(226).chr(128).chr(157)] = '&rdquo;';
        $xhtml[chr(226).chr(128)] = '&rdquo;';
        $xhtml[chr(226).chr(128).chr(147)] = '&mdash;';
        $xhtml[chr(226).chr(128).chr(166)] = '&hellip;';
        $xhtml[chr(195).chr(169)] = '&eacute;';
        $xhtml[chr(195).chr(175)] = '&iuml;';

        $text = strtr($text, $xhtml);
        $text = PHPWS_Text::fixAmpersand($text);
        return $text;
    }

    public function fixAmpersand($text)
    {
        return preg_replace('/&(?!\w+;)(?!#)/U', '&amp;\\1', $text);
    }

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
    public function profanityFilter($text)
    {
        if (!is_string($text)) {
            return PHPWS_Error::get(PHPWS_TEXT_NOT_STRING, 'core', 'PHPWS_Text::profanityFilter');
        }

        $words = unserialize(PROFANE_WORDS);

        foreach ($words as $matchWord=>$replaceWith) {
            $text = preg_replace("/$matchWord/i", $replaceWith, $text);
        }

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
    public function sentence($text, $stripNewlines = false)
    {
        if (!is_string($text)) {
            return PHPWS_Error::get(PHPWS_TEXT_NOT_STRING, 'core', 'PHPWS_Text::sentence');
        }

        return preg_split("/\r\n|\n/", $text);
    }// END FUNC sentence()



    /**
     * This is a replacement of the old breaker function
     * Looking for something faster and cleaner.
     *
     * Also, used logic from cor's (see bb2html code) function
     * to parse the <pre> tags.
     *
     * @author Matthew McNaney <matt at tux dot appstate dot edu>
     */
    public function breaker($text)
    {
        $do_not_break = array('/(<table.*>)\n/iU',
                              '/(<tbody.*>)\n/iU',
                              '/(<\/tbody.*>)\n/iU',
                              '/(<tr.*>)\n/iU',
                              '/(<\/tr>)\n/iU',
                              '/(<\/td>)\n/iU',
                              '/(<\/th>)\n/iU',
                              '/(<\/ol>)\n/iU',
                              '/(<ul.*>|<\/ul>)\n/iU',
                              '/(<\/li>)\n/iU',
                              '/(<area.*>)\n/iU',
                              '/(<\/area>)\n/iU',
                              '/(<\/p>)\n/iU',
                              '/(<br \/>|<br>)\n/iU',
                              '/(<\/dd>)\n/iU',
                              '/(<\/dt>)\n/iU',
                              '/(<\/h\d>)\n/iU',
                              '/(<blockquote>)\n/iU',
                              );

        $text = str_replace("\r\n", "\n", $text);
        $text = preg_replace($do_not_break, '\\1', $text);
        $text = preg_replace('/<pre>(.*)<\/pre>/Uies', "'<pre>' . str_replace(\"\n\", '[newline]', '\\1') . '</pre>'", $text);
        $text = nl2br($text);
        $text = str_replace('[newline]', "\n", $text);
        // removes extra breaks stuck in code tags by editors
        $text = preg_replace('/<code>(.*)<\/code>/Uies', "'<code>' . str_replace('<br />', '', '\\1') . '</code>'", $text);
        $text = preg_replace("/<br \/>([^\n])/", "<br />\n\\1", $text);
        return $text;
    }

    public function parseInput($text, $encode=ENCODE_PARSED_TEXT, $relative_links=MAKE_ADDRESSES_RELATIVE)
    {
        // Moved over from getPrint/parseOutput
        if ((bool)$relative_links) {
            PHPWS_Text::makeRelative($text, true, true);
        }

        if ($encode) {
            if (function_exists('iconv')) {
                $text = iconv('utf-8', 'utf-8', $text);
            } else {
                $text_tmp = utf8_encode($text);
                if (!empty($text_tmp)) {
                    $text = $text_tmp;
                }
            }
            $text = htmlentities($text, ENT_QUOTES, 'UTF-8');
        }
        return trim($text);
    }

    /**
     * Prepares text for display
     *
     * Should be used after retrieving data from the database
     *
     * @author                       Matthew McNaney <matt at tux dot appstate dot edu>
     * @param   string  text         Text to parse
     * @param   boolean decode       Whether entity_decoding should take place.
     * @param   boolean use_filters  If true, any filters requested in the text_settings file will be
     *                               run against the output text.
     * @return  string  text         Stripped text
     */
    public function parseOutput($text, $decode=ENCODE_PARSED_TEXT, $use_filters=false, $use_breaker=USE_BREAKER)
    {
        $t = new PHPWS_Text;
        $t->setText($text, $decode);
        $t->useFilters($use_filters);
        $text = $t->getPrint();
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
     * is used to pull database data, etc. Also, will ALWAYS return false
     * if it receives blank data.
     *
     * @author Matthew McNaney <matt at tux dot appstate dot edu>
     * @param  string  $userEntry Text to be checked
     * @param  string  $type      What type of comparison
     * @return boolean true on valid input, false on invalid input
     * @access public
     */
    public function isValidInput($userEntry, $type=null)
    {
        if (empty($userEntry) || !is_string($userEntry)) return false;

        switch ($type) {
        case 'chars_space':
            if (UTF8_MODE) {
                $preg = '/^[\w\s\pL]+$/ui';
            } else {
                $preg = '/^[\w\s]+$/ui';
            }
            if (preg_match($preg,$userEntry)) return true;
            else return false;
            break;

        case 'number':
            return is_numeric($userEntry);
            break;

        case 'url':
            if (preg_match('/^(http(s){0,1}:\/\/)\w([\.\w\-\/&?\+=~])+$/i', $userEntry)) return true;
            else return false;
            break;

        case 'email':
            if (preg_match('/^[\w]+([\.\w\-]+)*@[\w\-]+([\.\w\-]+)+$/i', $userEntry)) return true;
            else return false;
            break;

        case 'file':
            if (preg_match('/^[\w\.]+$/i',$userEntry)) return true;
            else return false;
            break;

        default:
            if (preg_match('/^[\w]+$/i',$userEntry)) return true;
            else return false;
            break;
        }
    }// END FUNC isValidInput()

    /**
     * Creates a mod_rewrite link that can be parsed by Apache
     */
    public function rewriteLink($subject, $module=null, $getVars=null, $target=null, $title=null, $class_name=null)
    {
        $link = PHPWS_Text::quickLink($subject, $module, $getVars, $target, $title, $class_name);
        $link->rewrite = true;
        return $link->get();
    }

    public function secureRewriteLink($subject, $module=null, $getVars=null, $target=null, $title=null, $class_name=null)
    {
        $link = PHPWS_Text::quickLink($subject, $module, $getVars, $target, $title, $class_name);
        $link->rewrite = true;
        $link->secure  = true;
        return $link->get();
    }


    /**
     * Creates a link to the previous referer (page)
     */
    public function backLink($title=null)
    {
        if (empty($title)) {
            $title = _('Return to previous page.');
        }

        if (!isset($_SERVER['HTTP_REFERER'])) {
            return null;
        }
        return sprintf('<a href="%s">%s</a>', $_SERVER['HTTP_REFERER'], $title);
    }

    public function quickLink($subject, $module=null, $getVars=null, $target=null, $title=null, $class_name=null)
    {
        $link = new PHPWS_Link($subject, $module, $getVars);
        $link->setTarget($target);

        if (!empty($title)) {
            $link->setTitle($title);
        }

        if (!empty($class_name)) {
            $link->setClass($class_name);
        }

        $link->rewrite = false;

        return $link;
    }

    /**
     * Returns a module link with the authkey attached
     */
    public function secureLink($subject, $module=null, $getVars=null, $target=null, $title=null, $class_name=null)
    {
        $link = PHPWS_Text::quickLink($subject, $module, $getVars, $target, $title, $class_name);
        $link->secure = true;
        return $link->get();
    }

    /**
     * Makes the index string for moduleLink. Can also be called alone
     * User module must be in use.
     *
     * @author Matthew McNaney <matt at tux dot appstate dot edu>
     * @param string  module      Name of module for link
     * @param array   getVars     Associative array of get values
     * @param boolean secure      If true, adds authkey to link
     * @param boolean add_base    If true, add the site url to the address
     * @param boolean convert_amp If true, use "&amp;" instead of "&"
     */
    public function linkAddress($module=null, $getVars=null, $secure=false, $add_base=false, $convert_amp=true, $rewrite=false)
    {
        $link = new PHPWS_Link(null, $module, $getVars);
        $link->secure      = $secure;
        $link->full_url    = $add_base;
        $link->convert_amp = $convert_amp;
        $link->rewrite     = $rewrite;
        return $link->getAddress();
    }

    public function rewriteAddress($module=null, $getVars=null, $secure=false, $add_base=false, $convert_amp=true)
    {
        return linkAddress($module, $getVars, $secure, $add_base, $convert_amp, true);
    }

    /**
     * Allows a quick link function for phpWebSite modules to the index.php.
     *
     * For local links ONLY. It adds the hub web address and index.php automatically.
     * You supply the name of the module and the variables.
     *
     * @author Matthew McNaney <matt at tux dot appstate dot edu>
     * @param string title String to appear as the 'click on' word(s)
     * @param string module Name of module to access
     * @param array getVars Associative array of GET variable to append to the link
     * @param string class_name String added to css class
     * @return string The complated link.
     */
    public function moduleLink($subject, $module=null, $getVars=null, $target=null, $title=null, $class_name=null)
    {
        $link = PHPWS_Text::quickLink($subject, $module, $getVars, $target, $title, $class_name);
        return $link->get();
    }// END FUNC moduleLink()


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
    public function checkLink($link, $ssl=false)
    {
        if (!stristr($link, '://')) {
            if ($ssl) {
                return 'https://'.$link;
            } else {
                return 'http://'.$link;
            }
        }
        else return $link;
    }// END FUNC checkLink()


    /**
     * Returns true if the text appears to have unslashed quotes or apostrophes
     *
     * @author Matthew McNaney <matt at tux dot appstate dot edu>
     * @param  string  $text Text to be checked for unslashed quotes or apostrophes
     * @return boolean true on success, false on failure
     * @access public
     */
    public function checkUnslashed($text)
    {
        if (preg_match("/[^\\\]+[\"']/", $text))
            return true;
        else
            return false;
    }// END FUNC checkUnslashed()

    /**
     * Removes slashes ONLY from quotes or apostrophes, nothing else
     *
     * @author Matthew McNaney <matt at tux dot appstate dot edu>
     * @param  string $text Text to remove slashes from
     * @return string $text Parsed text
     * @access public
     */
    public function stripSlashQuotes($text)
    {
        $text = str_replace("\'", "'", $text);
        $text = str_replace("\\\"", "\"", $text);
        return $text;
    }// END FUNC stripSlashQuotes()


    /**
     * Makes links relative to home site
     */
    public function makeRelative(&$text, $prefix=true, $inlink_only=false)
    {
        $address = addslashes(PHPWS_Core::getHomeHttp());
        if ($prefix) {
            $pre = './';
        } else {
            $pre = '';
        }

        if ($inlink_only) {
            $src = '@(src|href)="' . $address . '@';
            $rpl = "\\1=\"$pre";
            $text = preg_replace($src, $rpl, $text);
        } else {
            $text = str_replace($address, $pre, $text);
        }
    }

    /**
     * Parses text for SmartTags
     * @param string       text  Text to parse
     * @param allowed_mods mixed Array of allowed modules or string of one
     * @param ignore_mods  mixed Array of ignored modules or string of one
     */
    public function parseTag($text, $allowed_mods=null, $ignored_mods=null)
    {
        if (!isset($GLOBALS['embedded_tags'])) {
            return $text;
        }

        if (!empty($allowed_mods) && is_string($allowed_mods)) {
            $hold = $allowed_mods;
            unset($allowed_mods);
            $allowed_mods[] = $hold;
        }

        if (!empty($ignored_mods) && is_string($ignored_mods)) {
            $hold = $ignored_mods;
            unset($ignored_mods);
            $ignored_mods[] = $hold;
        }

        foreach ($GLOBALS['embedded_tags'] as $module => $null) {
            if ( (empty($allowed_mods) || in_array($module, $allowed_mods)) ) {
                if ( !empty($ignored_mods) && in_array($module, $ignored_mods) ) {
                    continue;
                }
                $search = "\[($module):([\w\s:\.\?\!]*)\]";
                $text = preg_replace_callback("/$search/Ui", 'getEmbedded', $text);
            }
        }

        return $text;
    }

    public function addTag($module, $function_names)
    {
        if (is_string($function_names)) {
            $GLOBALS['embedded_tags'][$module][] = $function_names;
        } elseif (is_array($function_names)) {
            $GLOBALS['embedded_tags'][$module] = $function_names;
        } else {
            return false;
        }
        return true;
    }

    public function getGetValues($query=null)
    {
        if (empty($query)) {
            if (isset($_SERVER['REDIRECT_QUERY_STRING'])) {
                $query = $_SERVER['REDIRECT_QUERY_STRING'];
            } elseif(isset($_SERVER['REDIRECT_URL'])) {
                if (dirname($_SERVER['PHP_SELF']) == '/') {
                    $rewrite = substr($_SERVER['REDIRECT_URL'], 1);
                } else {
                    $rewrite = str_ireplace(dirname($_SERVER['PHP_SELF']) .'/', '', $_SERVER['REDIRECT_URL']);
                }
                if (!empty($rewrite)) {
                    $re_array = explode('/', $rewrite);
                    $output['module'] = array_shift($re_array);

                    $count = 1;
                    $continue = 1;
                    $i = 0;
                    while(isset($re_array[$i])) {
                        $key = $re_array[$i];
                        $i++;
                        if (isset($re_array[$i])) {
                            $value = $re_array[$i];
                            $output[$key] = $value;
                        }
                        $i++;
                    }

                    return $output;
                }
            } else {
                $address = $_SERVER['REQUEST_URI'];
                $url = parse_url($address);
                extract($url);
            }
        } else {
            $query = str_replace('index.php?', '', $query);
        }

        if (empty($query)) {
            return null;
        }

        parse_str($query, $output);
        return $output;
    }

    /**
     * Parses an XML file into an array.
     *
     * This function was copied from php.net
     *
     * @author   mv at brazil dot com
     * @modified lorecarra at postino dot it
     * @modified Matt McNaney <matt at tux dot appstate dot edu>
     */
    public function xml2php($file, $level = 0)
    {
        $xml_parser = xml_parser_create();
        $contents = @file_get_contents($file);
        if (!$contents) {
            return false;
        }
        xml_parse_into_struct($xml_parser, $contents, $arr_vals);
        xml_parser_free($xml_parser);
        $result = PHPWS_Text::_orderXML($arr_vals);

        return getXMLLevel($result, $level);
    }


    /**
     * Further processes the data from xml2php into a more
     * useful array structure.
     *
     * @author Matt McNaney <matt at tux dot appstate dot edu>
     */
    public function _orderXML(&$arr_vals)
    {
        if (empty($arr_vals)) {
            return null;
        }
        while (@$xml_val = array_shift($arr_vals)) {
            $value = null;
            extract($xml_val);
            if ($type == 'close') {
                return $new_val;
            } elseif ($type == 'cdata') {
                continue;
            } elseif ($type == 'complete') {
                $insert = array('tag' => $tag, 'value' => $value);
            } else {
                $insert = array('tag' => $tag, 'value' => PHPWS_Text::_orderXML($arr_vals));
            }
            $new_val[] = $insert;
        }
        return $new_val;
    }

    public function tagXML($arr_vals)
    {
        if (empty($arr_vals)) {
            return null;
        }

        foreach ($arr_vals as $tag) {
            if (is_array($tag['value'])) {
                $new_arr[$tag['tag']][] = PHPWS_Text::tagXML($tag['value']);
            } else {
                $new_arr[$tag['tag']] = $tag['value'];
            }
        }
        return $new_arr;
    }

    /**
     * Returns a condensed version of text based on the maximum amount
     * of characters allowed.
     */
    public function condense($text, $max_characters=255)
    {
        $text = strip_tags($text);
        if (strlen($text) < $max_characters) {
            return $text;
        }

        $new_text = substr($text, 0, $max_characters);
        $last_newline = strrpos($new_text, "\n");

        if ($last_newline) {
            return substr($text, 0, $last_newline + 1);
        } else {
            $last_period = strrpos($new_text, '.');
            if ($last_period) {
                return substr($new_text, 0, $last_period + 1);
            } else {
                return $new_text;
            }
        }
    }

    public function collapseUrls($text, $limit=COLLAPSE_LIMIT)
    {
        if (!(int)$limit) {
            return $text;
        }

        return str_replace('\"', '"', preg_replace('/(<a .*?>\s*http(s)?:\/\/)(.*?)(\s*<\/a>)/ie',
                                                   "'\\1' . PHPWS_Text::shortenUrl('\\3', $limit) . '\\4'",
                                                   $text));
    }

    public function shortenUrl($url, $limit=COLLAPSE_LIMIT)
    {
        // + 3 takes the "..." into account
        if (!(int)$limit || strlen($url) < $limit + 3) {
            return $url;
        }

        $url_length = strpos($url, '?');

        if (!$url_length) {
            $url_length = floor($limit/2);
        }

        $pickup = $limit - $url_length;
        if ($pickup < 3) {
            $pickup = 3;
        }

        return substr($url, 0, $url_length) . '...' . substr($url, -1 * $pickup, $pickup);
    }

    /**
     * Returns a string composed of characters
     * @param integer characters Number of characters in string
     * @return string
     */
    public function randomString($characters=8)
    {
        $characters = (int)$characters;
        $alpha = '023456789abcdefghijkmnopqrstuvwxyz';
        srand((double)microtime()*1000000);
        
        for ($i = 0; $i < $characters; $i++) {
            $char = rand() % 34;
            $str[] = substr($alpha, $char, 1);
        }
        return implode('', $str);
    }

    /**
     * Changes an array into a serialized string for salting
     * a link's values
     */
    public function saltArray($values)
    {
        foreach ($values as $key=>$val) {
            $values[$key] = (string)$val;
        }
        return serialize($values);
    }

}//END CLASS PHPWS_Text


function getXMLLevel($xml, $level)
{
    if ($level < 1) {
        return $xml;
    } else {
        $sub = $xml[0]['value'];
        $level--;
        return getXMLLevel($sub, $level);
    }

}

/**
 * Function names and parameter values cannot be the same
 */
function getEmbedded($stuff)
{
    unset($stuff[0]);
    $module = $stuff[1];
    unset($stuff[1]);

    $parameters = explode(':', $stuff[2]);

    if (!isset($GLOBALS['embedded_tags'][$module])) {
        return;
    }

    if (count($GLOBALS['embedded_tags'][$module]) == 1 &&
        $parameters[0] != $GLOBALS['embedded_tags'][$module][0]) {
        $function_name = $GLOBALS['embedded_tags'][$module][0];
    } else {
        if (empty($parameters) || empty($parameters[0])) {
            return null;
        } else {
            $function_name = $parameters[0];
            unset($parameters[0]);
        }
    }

    if (!in_array($function_name, $GLOBALS['embedded_tags'][$module])) {
        return null;
    }

    $function_name = $module . '_' . $function_name;

    if (!function_exists($function_name)) {
        return null;
    }

    return call_user_func_array($function_name, $parameters);
}
?>
