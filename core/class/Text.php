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

PHPWS_Core::configRequireOnce('core', 'text_settings.php');

class PHPWS_Text {
    var $use_profanity  = ALLOW_PROFANITY;
    var $use_breaker    = TRUE;
    var $use_strip_tags = TRUE;
    var $use_bbcode     = ALLOW_BB_CODE;
    var $_allowed_tags  = NULL;

    function PHPWS_Text($text=NULL, $encoded=FALSE)
    {
        $this->resetAllowedTags();
        $this->setText($text, $encoded);
    }

    function setText($text, $decode=TRUE)
    {
        if (empty($text) || !is_string($text)) {
            return;
        }

        if ($decode) {
            $this->text = html_entity_decode($text, ENT_QUOTES);
        } else {
            $this->text = $text;
        }
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

        return $text;
    }

    /**
     * Cleans up MSWord smart quotes
     * Gleaned from:
     *  http://us4.php.net/manual/en/function.htmlentities.php
     *
     * @author wwb at 3dwargamer dot net
     */
    function CleanupSmartQuotes($text)
    {
        $badwordchars=array(
                            chr(145),
                            chr(146),
                            chr(147),
                            chr(148),
                            chr(151)
                            );
        $fixedwordchars=array(
                              "'",
                              "'",
                              '&quot;',
                              '&quot;',
                              '&mdash;'
                              );
        return str_replace($badwordchars,$fixedwordchars,$text);
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
        $text = preg_replace('/&(?!\w+;)(?!#)/U', '&amp;\\1', $text);

        return $text;
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
                              '/(<tr.*>)\n/iU',
                              '/(<\/tr>)\n/i',
                              '/(<\/td>)\n/i',
                              '/(<\/th>)\n/i',
                              '/(<\/li>)\n/i',
                              '/(<\/p>)\n/i',
                              '/(<br \/>)\n/i',
                              '/(<\/dd>)\n/i',
                              '/(<\/dt>)\n/i',
                              '/(<\/h\d>)\n/i',
                              '/(<blockquote>)\n/i',                      
                              );

        $pre = array();
        $i=0;
        while ($pre_str = stristr($text,'<pre>')) {
            $pre_str = substr($pre_str,0,strpos($pre_str,'</pre>')+6);
            $text = str_replace($pre_str, "***pre_string***$i", $text);
            $pre[$i] = str_replace("\r\n","\n",$pre_str);
            $i++;
        }

        $text = str_replace("\r\n", "\n", $text);

        $text = preg_replace($do_not_break, '\\1', $text); 
        $text = nl2br($text);
        $cp = count($pre)-1;
        for($i=0;$i <= $cp;$i++) {
            $text = str_replace("***pre_string***$i", '<pre>'.substr($pre[$i],5,-6).'</pre>', $text);
        }

        return $text;
    }

    function parseInput($text, $encode=TRUE){
        $text = trim($text);

        $text = PHPWS_Text::CleanupSmartQuotes($text);
        if ($encode) {
            $text = htmlentities($text, ENT_QUOTES, 'UTF-8');
        }
        if (MAKE_ADDRESSES_RELATIVE) {
            PHPWS_Text::makeRelative($text);
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
    function parseOutput($text, $decode=TRUE)
    {
        $t = & new PHPWS_Text;
        $t->setText($text, $decode);

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
            if (preg_match("/^[\w\s]+$/i",$userEntry)) return TRUE;
            else return FALSE;
            break;

        case 'number':
            return is_numeric($userEntry);
            break;

        case 'url':
            if (preg_match("/^(http(s){0,1}:\/\/)[\w-]+(\.[\w-]+|\/)/i", $userEntry)) return TRUE;
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
     *
     * @author Matthew McNaney <matt at tux dot appstate dot edu>
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
        elseif ($target=="index")
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

    function makeRelative(&$text){
        $address = addslashes(PHPWS_Core::getHomeHttp());
        $text = str_replace($address, './', $text);
    }

    /**
     * Parses text for SmartTags
     */
    function parseTag($text, $allowed_mods=NULL)
    {
        if (!isset($GLOBALS['embedded_tags'])) {
            return $text;
        }

        foreach ($GLOBALS['embedded_tags'] as $module => $ignore) {
            if (empty($allowed_mods) || (is_array($allowed_mods) &&
                in_array($module, $allowed_mods))) {
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
            $results = file_get_contents(PHPWS_HOME_DIR . 'config/core/smiles.pak');
            if (empty($results)) {
                return $bbcode;
            }
            $smiles = explode("\n", $results);
            foreach ($smiles as $row){
                $icon = explode('=+:', $row);
        
                $search[] = '@' . preg_quote($icon[2]) . '@';
                $replace[] = sprintf('<img src="%simages/core/smilies/%s" title="%s" />',
                                     PHPWS_HOME_HTTP, $icon[0], $icon[1]);

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
    function bb2html($bb2html, $title) {
        /*      pre-formatted text (even bbcode inside [pre] text will remain untouched, as it should be)
                there may be multiple <pre> blocks, so we grab them all and create an array     */
        $pre = array(); $i=0;
        while ($pre_str = stristr($bb2html,'[pre]')) {
            $pre_str = substr($pre_str,0,strpos($pre_str,'[/pre]')+6);
            $bb2html = str_replace($pre_str, "***pre_string***$i", $bb2html);
            $pre[$i] = str_replace("\r\n","\n",$pre_str);
            $i++;
        }

        // now the bbcode proper..
        
        // grab any *real* square brackets first, store em
        $bb2html = str_replace('[[', '**$@$**', $bb2html);
        $bb2html = str_replace(']]', '**@^@**', $bb2html);
        
        // news headline block
        $bb2html = str_replace('[news]', 
                               '<table width="20%" border="0" align="right"><tr><td align="center"><span class="news">', $bb2html);
        $bb2html = str_replace('[/news]', '</span></td></tr></table>', $bb2html);
        
        // references - we need to create the whole string first, for the str_replace
        $r1 = '<a href="#refs-'.$title.'" title="'.$title.'"><font class="ref"><sup>';
        $bb2html = str_replace('[ref]', $r1 , $bb2html);
        $r2 = '<p id="refs-'.$title.'"></p>
<font class="ref"><b><u><a title="back to the text" href="javascript:history.go(-1)">references:</a></u><br /><br />1: </b></font><font class="reftext">';
        $bb2html = str_replace('[reftxt1]', $r2 , $bb2html);
        $bb2html = str_replace('[reftxt2]', '<font class="ref"><b>2: </b></font><font class="reftext">', $bb2html);
        $bb2html = str_replace('[reftxt3]', '<font class="ref"><b>3: </b></font><font class="reftext">', $bb2html);
        $bb2html = str_replace('[reftxt4]', '<font class="ref"><b>4: </b></font><font class="reftext">', $bb2html);
        $bb2html = str_replace('[reftxt5]', '<font class="ref"><b>5: </b></font><font class="reftext">', $bb2html);
        $bb2html = str_replace('[/ref]', '</sup></font></a>', $bb2html);
        $bb2html = str_replace('[/reftxt]', '</font>', $bb2html);
        
        // ordinary transformations..
        // we rely on the browser producing \r\n (DOS) carriage returns, as per spec
        //    $bb2html = str_replace("\r",'<br />', $bb2html);  // the \n remains, makes the raw html readable
        $bb2html = str_replace('[b]', '<b>', $bb2html);
        $bb2html = str_replace('[/b]', '</b>', $bb2html);
        $bb2html = str_replace('[i]', '<i>', $bb2html);
        $bb2html = str_replace('[/i]', '</i>', $bb2html);
        $bb2html = str_replace('[u]', '<u>', $bb2html);
        $bb2html = str_replace('[/u]', '</u>', $bb2html);
        $bb2html = str_replace('[big]', '<span class="bigger">', $bb2html);
        $bb2html = str_replace('[/big]', '</span>', $bb2html);
        $bb2html = str_replace('[sm]', '<span class="smaller">', $bb2html);
        $bb2html = str_replace('[/sm]', '</span>', $bb2html);
        
        
        // tables (couldn't resist this, too handy)
        $bb2html = str_replace('[t]', '<table width="100%" border="0" cellspacing="0" cellpadding="0">', $bb2html);     
        $bb2html = str_replace('[bt]', '<table width="100%" border="1" cellspacing="0" cellpadding="3">', $bb2html);
        $bb2html = str_replace('[st]', '<table width="100%" border="0" cellspacing="3" cellpadding="3">', $bb2html);    
        $bb2html = str_replace('[/t]', '</table>', $bb2html);
        $bb2html = str_replace('[c]', '<td valign=top>', $bb2html);     // cell data
        $bb2html = str_replace('[/c]', '</td>', $bb2html);
        $bb2html = str_replace('[r]', '<tr>', $bb2html);        // a row
        $bb2html = str_replace('[/r]', '</tr>', $bb2html);
        
        // a simple list
        $bb2html = str_replace('[*]', '<li>', $bb2html);
        $bb2html = str_replace('[list]', '<ul>', $bb2html);
        $bb2html = str_replace('[/list]', '</ul>', $bb2html);
        
        if (ALLOW_BB_SMILIES) {
            $bb2html = PHPWS_Text::getSmilie($bb2html);
        }

        // anchors and stuff..
        if (ALLOW_BB_IMAGES) {
            $bb2html = str_replace('[img]', '<img border="0" src="', $bb2html);
            $bb2html = str_replace('[/img]', '" alt="an image" />', $bb2html);
        } else {
            $bb2html = str_replace('[img]', '[' . _('No Images') . ']', $bb2html);
            $bb2html = str_replace('[/img]', '[/' . _('No Images') . ']', $bb2html);
        }


        $bb2html = preg_replace('/\[url="(.*)"\](.*)\[\/url\]/Ui', '<a target="_blank" href="\\1">\\2</a>', $bb2html);
        $bb2html = preg_replace('/\[quote="(.*)"\]/Ui', '<blockquote><h3>\\1 ' . _('wrote') . ':</h3>', $bb2html);
        $bb2html = str_replace('[quote]', '<blockquote>', $bb2html);
        $bb2html = str_replace('[/quote]', '</blockquote>', $bb2html);

        // code
        $bb2html = str_replace('[code]', '<div class="simcode">', $bb2html);
        $bb2html = str_replace('[coderz]', '<div class="code">', $bb2html);
        $bb2html = str_replace('[/code]', '</div>', $bb2html);
        $bb2html = str_replace('[/coderz]', '</div>', $bb2html); // you can complete either way, it's all [/code]
        
        // divisions..
        $bb2html = str_replace('[hr]', '<hr />', $bb2html);
        $bb2html = str_replace('[block]', '<blockquote>', $bb2html);
        $bb2html = str_replace('[/block]', '</blockquote>', $bb2html);
        
        // dropcaps. five flavours, small up to large.. [dc1]I[/dc] >> [dc5]W[/dc]
        $bb2html = str_replace('[dc1]', '<span class="dropcap1">', $bb2html);
        $bb2html = str_replace('[dc2]', '<span class="dropcap2">', $bb2html);
        $bb2html = str_replace('[dc3]', '<span class="dropcap3">', $bb2html);
        $bb2html = str_replace('[dc4]', '<span class="dropcap4">', $bb2html);
        $bb2html = str_replace('[dc5]', '<span class="dropcap5">', $bb2html);
        $bb2html = str_replace('[/dc]', '<dc></span>', $bb2html);
        
        // special characters (html entity encoding) ..
        $bb2html = str_replace('[sp]', '&nbsp;', $bb2html);
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

        return $bb2html;
    }/* end function bb2html($bb2html, $title) */

    function getGetValues()
    {
        if (isset($_SERVER['REDIRECT_QUERY_STRING'])) {
            $query = $_SERVER['REDIRECT_QUERY_STRING'];
        } else {
            $url = parse_url($_SERVER['REQUEST_URI']);
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
        $contents = file_get_contents($file);
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
