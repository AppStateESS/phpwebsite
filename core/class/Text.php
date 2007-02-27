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

if (!defined('PHPWS_HOME_HTTP')) {
    define('PHPWS_HOME_HTTP', './');
 }

PHPWS_Core::configRequireOnce('core', 'text_settings.php');

class PHPWS_Text {
    var $use_profanity  = ALLOW_PROFANITY;
    var $use_breaker    = true;
    var $use_strip_tags = true;
    var $use_bbcode     = ALLOW_BB_CODE;
    var $use_smilies    = false;
    var $fix_anchors    = FIX_ANCHORS;
    var $collapse_urls  = COLLAPSE_URLS;
    var $_allowed_tags  = NULL;


    function PHPWS_Text($text=NULL, $encoded=FALSE, $smilies=false)
    {
        $this->resetAllowedTags();
        $this->setText($text, $encoded, $smilies);
    }

    function setText($text, $decode=true, $smilies=false)
    {
        if (empty($text) || !is_string($text)) {
            return;
        }

        $this->use_smilies = $smilies;

        if ($decode) {
            if (version_compare(phpversion(), '5.0.0', '>=')) {
                $this->text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
            } else {
                $this->text = $this->decode_entities($text);
            }
        } else {
            $this->text = $text;
        }
    }

    /**
     * This is a modified copy of a function from 'derernst at gmx dot ch' at php.net
     * I added the utf8_convert. His work was based on others. Please see notes under
     * html_entity_decode
     * @ author derernst at gmx dot ch
     * @author Matthew McNaney <mcnaney at gmail dot com>
     */

    function decode_entities($text, $quote_style = ENT_COMPAT) {
        if (!function_exists('html_entity_decode')) {
            $text = html_entity_decode($text, $quote_style, 'ISO-8859-1'); // NOTE: UTF-8 does not work!
        }
        else {
            $trans_tbl = get_html_translation_table(HTML_ENTITIES, $quote_style);
            $trans_tbl = array_flip($trans_tbl);
            $text = strtr($text, $trans_tbl);
        }
        $text = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $text);
        $text = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $text);
        return utf8_encode($text);
    }


    function useBBcode($use = TRUE) {
        $this->use_bbcode = $use;
    }

    function useProfanity($use = TRUE)
    {
        $this->use_profanity = (bool)$use;
    }

    function useBreaker($use = TRUE)
    {
        $this->use_breaker = (bool)$use;
    }

    function useStripTags($use = TRUE)
    {
        $this->use_strip_tags = (bool)$use;
    }

    function addAllowedTags($tags)
    {
        if (is_array($tags)) {
            $this->_allowed_tags .= implode('', $tags);
        } else {
            $this->_allowed_tags .= $tags;
        }
    }

    function setAllowedTags($tags)
    {
        if (is_array($tags)) {
            $this->_allowed_tags = implode('', $tags);
        } else {
            $this->_allowed_tags = $tags;
        }
    }

    function resetAllowedTags()
    {
        $this->_allowed_tags = preg_replace('/\s/','',  PHPWS_ALLOWED_TAGS);
    }

    function clearAllowedTags()
    {
        $this->_allowed_tags = NULL;
    }

    function getPrint()
    {
        if (empty($this->text)) {
            return NULL;
        }
        
        $text = $this->text;
        
        if ($this->use_bbcode) {
            $text = PHPWS_Text::bb2html($text, 'whatsthat');
        }

        if (!$this->use_profanity) {
            $text = PHPWS_Text::profanityFilter($text);
        }

        if ($this->use_strip_tags) {
            $text = strip_tags($text, $this->_allowed_tags);
        }

        $text = PHPWS_Text::encodeXHTML($text);

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

    /**
     * Fixes plain anchors. Makes them relative to the current page.
     */
    function fixAnchors($text)
    {
        $home_http = PHPWS_Core::getCurrentUrl();

        return preg_replace('/href="#(\w+)"/',
                            sprintf('href="%s#\\1"', $home_http),
                            $text);
    }

    function encodeXHTML($text){
        $xhtml['™']    = '&trade;';
        $xhtml['•']    = '&bull;';
        $xhtml['°']    = '&deg;';
        $xhtml['©']    = '&copy;';
        $xhtml['®']    = '&reg;';
        $xhtml['…']    = '&hellip;';

        $xhtml['\$']   = '&#x24;';
        $xhtml['<br>'] = '<br />';
        $xhtml['\'']    = '&#39;';
        $text = strtr($text, $xhtml);
        $text = PHPWS_Text::fixAmpersand($text);
        return $text;
    }

    function fixAmpersand($text)
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
    function profanityFilter($text) {
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
    function sentence($text, $stripNewlines = FALSE){
        if (!is_string($text)) {
            return PHPWS_Error::get(PHPWS_TEXT_NOT_STRING, 'core', 'PHPWS_Text::sentence');
        }

        return preg_split("/\r\n|\n/", $text);
    }// END FUNC sentence()



    /**
     * This is a replacement of the old breaker function
     * Looking for something faster and cleaner.
     *
     * Also, used logic from cor's (see bb2html code below) function
     * to parse the <pre> tags.
     *
     * @author Matthew McNaney <matt at tux dot appstate dot edu>
     */
    function breaker($text)
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
        return $text;
    }

    function parseInput($text, $encode=TRUE){
        $text = trim($text);

        if (MAKE_ADDRESSES_RELATIVE) {
            PHPWS_Text::makeRelative($text, true, true);
        }

        if ($encode) {
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
     * @return  string  text         Stripped text
     */
    function parseOutput($text, $decode=TRUE, $smilies=false)
    {
        $t = & new PHPWS_Text;
        $t->setText($text, $decode, $smilies);

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
            if (preg_match('/^[\w\s]+$/i',$userEntry)) return TRUE;
            else return FALSE;
            break;

        case 'number':
            return is_numeric($userEntry);
            break;

        case 'url':
            if (preg_match('/^(http(s){0,1}:\/\/)\w([\.\w\-\/&?\+=])+$/i', $userEntry)) return TRUE;
            else return FALSE;
            break;

        case 'email':
            if (preg_match('/^[\w]+([\.\w\-]+)*@[\w\-]+([\.\w\-]+)+$/i', $userEntry)) return TRUE;
            else return FALSE;
            break;

        case 'file':
            if (preg_match('/^[\w\.]+$/i',$userEntry)) return TRUE;
            else return FALSE;
            break;

        default:
            if (preg_match('/^[\w]+$/i',$userEntry)) return TRUE;
            else return FALSE;
            break;
        }
    }// END FUNC isValidInput()

    /**
     * Creates a mod_rewrite link that can be parsed by Apache
     *
     */
    function rewriteLink($subject, $module, $id, $page=NULL)
    {
        if ( preg_match('/\W/', $module) ||
             preg_match('/\W/', $id) ||
             (!empty($page) && preg_match('/\W/', $page))
             ) {
            return NULL;
        }
            

        if ((bool)MOD_REWRITE_ENABLED == FALSE) {
            $vars['id'] = $id;
            if ($page) {
                $vars['page'] = $page;
            }

            return PHPWS_Text::moduleLink($subject, $module, $vars);
        } else {
            if ($page) {
                return sprintf('<a href="%s/%s/%s">%s</a>', $module, $id, $page, $subject);
            } else {
                return sprintf('<a href="%s/%s">%s</a>', $module, $id, $subject);
            }
        }
    }

    /**
     * Creates a link to the previous referer (page)
     */
    function backLink($title=NULL)
    {
        if (empty($title)) {
            translate('core');
            $title = _('Return to previous page.');
            translate();
        }

        if (!isset($_SERVER['HTTP_REFERER'])) {
            return NULL;
        }
        return sprintf('<a href="%s">%s</a>', $_SERVER['HTTP_REFERER'], $title);
    }

    /**
     * Returns a module link with the authkey attached
     */
    function secureLink($subject, $module=NULL, $getVars=NULL, $target=NULL, $title=NULL){
        if (Current_User::isLogged()) {
            $getVars['authkey'] = Current_User::getAuthKey();
        }

        return PHPWS_Text::moduleLink($subject, $module, $getVars, $target, $title);
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
    function linkAddress($module=NULL, $getVars=NULL, $secure=FALSE, $add_base=FALSE, $convert_amp=TRUE){
        if (Current_User::isLogged() && $secure) {
            $getVars['authkey'] = Current_User::getAuthKey();
        }

        if ($add_base == TRUE) {
            $link[] = PHPWS_HOME_HTTP;
        }

        $link[] = 'index.php';
    
        if (isset($module)){
            $link[] = '?';
            $vars[] = "module=$module";
        }
    
        if (is_array($getVars)){
            foreach ($getVars as $var_name=>$value)
                $vars[] = $var_name . '=' . $value;
        }
    
        if ($convert_amp) {
            $amp = '&amp;';
        } else {
            $amp = '&';
        }

        if (isset($vars)) {
            $link[] = implode($amp, $vars);
        }

        return implode('', $link);
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
     * @return string The complated link.
     */
    function moduleLink($subject, $module=NULL, $getVars=NULL, $target=NULL, $title=NULL){
        $link[] = '<a ';

        if (isset($title))
            $link[] = 'title="' . strip_tags($title) . '" ';

        $link[] = 'href="./';
        $link[] = PHPWS_Text::linkAddress($module, $getVars);
        $link[] = '"';
        if ($target=='blank' || $target === TRUE)
            $link[] = ' target="_blank" ';
        elseif ($target=='index')
            $link[] = ' target="index" ';

        $link[] = '>';

        return implode('', $link) . strip_tags($subject, '<img>') . '</a>';
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


    /**
     * Makes links relative to home site
     */
    function makeRelative(&$text, $prefix=true, $inlink_only=false){
        $address = addslashes(PHPWS_Core::getHomeHttp());
        if ($prefix) {
            $replace = './';
        } else {
            $replace = '';
        }

        if ($inlink_only) {
            $address = '="' . $address;
            $replace = '="' . $replace;
        }

        $text = str_replace($address, $replace, $text);
    }

    /**
     * Parses text for SmartTags
     * @param string       text  Text to parse
     * @param allowed_mods mixed Array of allowed modules or string of one
     * @param ignore_mods  mixed Array of ignored modules or string of one
     */
    function parseTag($text, $allowed_mods=null, $ignored_mods=null)
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

    function addTag($module, $function_names)
    {
        if (is_string($function_names)) {
            $GLOBALS['embedded_tags'][$module][] = $function_names;
        } elseif (is_array($function_names)) {
            $GLOBALS['embedded_tags'][$module] = $function_names;
        } else {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Gets smilie graphics for bbcode
     *
     * @author Matthew McNaney <matt at tux dot appstate dot edu>
     */
    function getSmilie($bbcode)
    {
        if (isset($GLOBALS['Smilie_Search'])) {
            $search = &$GLOBALS['Smilie_Search']['code'];
            $replace = &$GLOBALS['Smilie_Search']['img'];
        } else {
            $results = trim(file_get_contents('config/core/smiles.pak'));
            if (empty($results)) {
                return $bbcode;
            }
            $smiles = explode("\n", $results);
            foreach ($smiles as $row) {
                $icon = explode('=+:', $row);
                
                if (count($icon) < 3) {
                    continue;
                }
                $search[] = '@' . preg_quote($icon[2]) . '@';
                $replace[] = sprintf('<img src="images/core/smilies/%s" title="%s" alt="%s" />',
                                     $icon[0], $icon[1], $icon[1]);

            }
            $GLOBALS['Smilie_Search']['code'] = $search;
            $GLOBALS['Smilie_Search']['img'] = $replace;
        }

        $bbcode = preg_replace($search, $replace, $bbcode);
        return $bbcode;
    }

    /**
     * Parses bbcode
     *
     * This is a copy of corzblog's (http://corz.org/) bb parsing
     * code. Compared to Pear's bbcode parser, it is easier to use and
     * edit. It has been altered to work specifically with phpWebSite.
     * 
     * @author (or at corz.org
     * @modified Matt McNaney <matt at tux dot appstate dot edu>
     */
    function bb2html($bb2html, $title)
    {
        translate('core');
        /*      pre-formatted text (even bbcode inside [pre] text will remain untouched, as it should be)
         there may be multiple <pre> blocks, so we grab them all and create an array     */
        $pre = array(); $i=0;
        while ($pre_str = stristr($bb2html,'[pre]')) {
            $pre_str = substr($pre_str,0,stripos($pre_str,'[/pre]')+6);
            $bb2html = str_ireplace($pre_str, "***pre_string***$i", $bb2html);
            $pre[$i] = str_replace("\r\n","\n",$pre_str);
            $i++;
        }

        // now the bbcode proper..
        
        // grab any *real* square brackets first, store em
        $bb2html = str_replace('[[', '**$@$**', $bb2html);
        $bb2html = str_replace(']]', '**@^@**', $bb2html);
        
        // news headline block
        $bb2html = str_ireplace('[news]', 
                               '<table width="20%" border="0" align="right"><tr><td align="center"><span class="news">', $bb2html);
        $bb2html = str_ireplace('[/news]', '</span></td></tr></table>', $bb2html);
        
        // references - we need to create the whole string first, for the str_replace
        $r1 = '<a href="#refs-'.$title.'" title="'.$title.'"><font class="ref"><sup>';
        $bb2html = str_ireplace('[ref]', $r1 , $bb2html);
        $r2 = '<p id="refs-'.$title.'"></p>
<font class="ref"><b><u><a title="back to the text" href="javascript:history.go(-1)">references:</a></u><br /><br />1: </b></font><font class="reftext">';
        $bb2html = str_ireplace('[reftxt1]', $r2 , $bb2html);
        $bb2html = str_ireplace('[reftxt2]', '<font class="ref"><b>2: </b></font><font class="reftext">', $bb2html);
        $bb2html = str_ireplace('[reftxt3]', '<font class="ref"><b>3: </b></font><font class="reftext">', $bb2html);
        $bb2html = str_ireplace('[reftxt4]', '<font class="ref"><b>4: </b></font><font class="reftext">', $bb2html);
        $bb2html = str_ireplace('[reftxt5]', '<font class="ref"><b>5: </b></font><font class="reftext">', $bb2html);
        $bb2html = str_ireplace('[/ref]', '</sup></font></a>', $bb2html);
        $bb2html = str_ireplace('[/reftxt]', '</font>', $bb2html);
        
        // ordinary transformations..
        // we rely on the browser producing \r\n (DOS) carriage returns, as per spec
        //    $bb2html = str_ireplace("\r",'<br />', $bb2html);  // the \n remains, makes the raw html readable
        $bb2html = str_ireplace('[b]', '<b>', $bb2html);
        $bb2html = str_ireplace('[/b]', '</b>', $bb2html);
        $bb2html = str_ireplace('[i]', '<i>', $bb2html);
        $bb2html = str_ireplace('[/i]', '</i>', $bb2html);
        $bb2html = str_ireplace('[u]', '<u>', $bb2html);
        $bb2html = str_ireplace('[/u]', '</u>', $bb2html);
        $bb2html = str_ireplace('[big]', '<span class="bigger">', $bb2html);
        $bb2html = str_ireplace('[/big]', '</span>', $bb2html);
        $bb2html = str_ireplace('[sm]', '<span class="smaller">', $bb2html);
        $bb2html = str_ireplace('[/sm]', '</span>', $bb2html);
        
        
        // tables (couldn't resist this, too handy)
        $bb2html = str_ireplace('[t]', '<table width="100%" border="0" cellspacing="0" cellpadding="0">', $bb2html);     
        $bb2html = str_ireplace('[bt]', '<table width="100%" border="1" cellspacing="0" cellpadding="3">', $bb2html);
        $bb2html = str_ireplace('[st]', '<table width="100%" border="0" cellspacing="3" cellpadding="3">', $bb2html);    
        $bb2html = str_ireplace('[/t]', '</table>', $bb2html);
        $bb2html = str_ireplace('[c]', '<td valign=top>', $bb2html);     // cell data
        $bb2html = str_ireplace('[/c]', '</td>', $bb2html);
        $bb2html = str_ireplace('[r]', '<tr>', $bb2html);        // a row
        $bb2html = str_ireplace('[/r]', '</tr>', $bb2html);
        
        // a simple list
        $bb2html = str_replace('[*]', '<li>', $bb2html);
        $bb2html = str_ireplace('[list]', '<ul>', $bb2html);
        $bb2html = str_ireplace('[/list]', '</ul>', $bb2html);
        
        if (ALLOW_BB_SMILIES && $this->use_smilies) {
            $bb2html = PHPWS_Text::getSmilie($bb2html);
        }

        // anchors and stuff..
        if (ALLOW_BB_IMAGES) {
            $bb2html = str_ireplace('[img]', '<img border="0" src="', $bb2html);
            $bb2html = str_ireplace('[/img]', '" alt="an image" />', $bb2html);
        } else {
            $bb2html = str_ireplace('[img]', '[' . _('No Images') . ']', $bb2html);
            $bb2html = str_ireplace('[/img]', '[/' . _('No Images') . ']', $bb2html);
        }


        $bb2html = preg_replace('/\[url=([\w:\/\.\-&=\s\?]+)\](.*)\[\/url\]/Ui', '<a target="_blank" href="\\1">\\2</a>', $bb2html);
        
        if (BBCODE_QUOTE_TYPE == 'fieldset') {
            $bb2html = preg_replace(array('/\[quote="(.*)"\]/isU', '/\[quote(?!=".*").*\]/isU', '/\[\/quote\]/isU'),
                                    array('<fieldset class="quote">&#013;<legend>' . _('\1 wrote') . ':</legend>&#013;\2', '<fieldset>&#013;', '</fieldset>&#013;'), $bb2html);
        } elseif (BBCODE_QUOTE_TYPE == 'blockquote') {
            $bb2html = preg_replace('/\[quote="(.*)"\]/Ui', '<blockquote class="quote"><h3>' . _('\1 wrote') . ':</h3>', $bb2html);
            $bb2html = str_ireplace('[quote]', '<blockquote class="quote">', $bb2html);
            $bb2html = str_ireplace('[/quote]', '</blockquote>', $bb2html);
        }
 
        // code
        $bb2html = preg_replace('/\[code\](.*)\[\/code\]/Uies', "'<code>' . stripslashes(htmlentities('\\1')) . '</code>'", $bb2html);

        // divisions..
        $bb2html = str_ireplace('[hr]', '<hr />', $bb2html);
        $bb2html = str_ireplace('[block]', '<blockquote>', $bb2html);
        $bb2html = str_ireplace('[/block]', '</blockquote>', $bb2html);
        
        // dropcaps. five flavours, small up to large.. [dc1]I[/dc] >> [dc5]W[/dc]
        $bb2html = str_ireplace('[dc1]', '<span class="dropcap1">', $bb2html);
        $bb2html = str_ireplace('[dc2]', '<span class="dropcap2">', $bb2html);
        $bb2html = str_ireplace('[dc3]', '<span class="dropcap3">', $bb2html);
        $bb2html = str_ireplace('[dc4]', '<span class="dropcap4">', $bb2html);
        $bb2html = str_ireplace('[dc5]', '<span class="dropcap5">', $bb2html);
        $bb2html = str_ireplace('[/dc]', '<dc></span>', $bb2html);
        
        // special characters (html entity encoding) ..
        $bb2html = str_ireplace('[sp]', '&nbsp;', $bb2html);
        $bb2html = str_replace('[<]', '&lt;', $bb2html);
        $bb2html = str_replace('[>]', '&gt;', $bb2html);

        //spoiler
        $bb2html = preg_replace('/\[spoiler\](.*)\[\/spoiler\]/Ui', _('Spoiler') . ':<br /><span class="spoiler">\\1</span>', $bb2html);
        // get back those square brackets..
        $bb2html = str_replace('**$@$**', '[', $bb2html);
        $bb2html = str_replace('**@^@**', ']', $bb2html);
        
        // re-insert the preformatted text blocks..
        $cp = count($pre)-1;
        for($i=0;$i <= $cp;$i++) {
            $bb2html = str_replace("***pre_string***$i", '<pre>'.substr($pre[$i],5,-6).'</pre>', $bb2html);
        }
        translate();
        return $bb2html;
    }
    /* end function bb2html($bb2html, $title) */

    function getGetValues($address=NULL)
    {
        if (empty($address) && isset($_SERVER['REDIRECT_QUERY_STRING'])) {
            $query = $_SERVER['REDIRECT_QUERY_STRING'];
        } else {
            if (empty($address)) {
                $address = $_SERVER['REQUEST_URI'];
            }

            $url = parse_url($address);
            extract($url);
        }

        if (empty($query)) {
            return NULL;
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
    function xml2php($file, $level = 0) {
        $xml_parser = xml_parser_create();
        $contents = @file_get_contents($file);
        if (!$contents) {
            return FALSE;
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
    function _orderXML(&$arr_vals) {
        if (empty($arr_vals)) {
            return NULL;
        }
        while (@$xml_val = array_shift($arr_vals)) {
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

    function tagXML($arr_vals)
    {
        if (empty($arr_vals)) {
            return NULL;
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
    function condense($text, $max_characters=255)
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

    function collapseUrls($text, $limit=COLLAPSE_LIMIT)
    {
        if (!(int)$limit) {
            return $text;
        }

        return str_replace('\"', '"', preg_replace('/(<a .*?>http(s)?:\/\/)(.*?)(<\/a>)/ie',
                                                   "'\\1' . PHPWS_Text::shortenUrl('\\3', $limit) . '\\4'",
                                                   $text));
    }

    function shortenUrl($url, $limit=30)
    {
        if (!(int)$limit || strlen($url) < $limit) {
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
            return NULL;
        } else {
            $function_name = $parameters[0];
            unset($parameters[0]);
        }
    }


    if (!in_array($function_name, $GLOBALS['embedded_tags'][$module])) {
        return NULL;
    }

    $function_name = $module . '_' . $function_name;

    if (!function_exists($function_name)) {
        return NULL;
    }

    return call_user_func_array($function_name, $parameters);
}
?>
