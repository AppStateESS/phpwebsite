<?php

/**
 * Parses bbcode
 *
 * This is a copy of corzblog's (http://corz.org/) bb parsing
 * code. Compared to Pear's bbcode parser, it is easier to use and
 * edit. It has been altered to work specifically with phpWebSite.
 *
 * @author (or at corz.org
 * @modified Matt McNaney <mcnaney at gmail dot com>
 */


// If TRUE, then 'smilies' will be parsed.
if (!defined('ALLOW_BB_SMILIES')) {
    define('ALLOW_BB_SMILIES', true);
}

// If TRUE, users can post with the [img] tag
if (!defined('ALLOW_BB_IMAGES')) {
    define('ALLOW_BB_IMAGES', true);
}

// Either "fieldset" or "blockquote"
if (!defined('BBCODE_QUOTE_TYPE')) {
    define('BBCODE_QUOTE_TYPE', 'fieldset');
}

function bb_filter($bb2html)
{
    $title = time();
    /*      pre-formatted text (even bbcode inside [pre] text will remain untouched, as it should be)
     there may be multiple <pre> blocks, so we grab them all and create an array     */
    $pre = array(); $i=0;
    while ($pre_str = stristr($bb2html,'[pre]')) {
        $pre_str = substr($pre_str,0,stripos($pre_str,'[/pre]')+6);
        $bb2html = str_ireplace($pre_str, "***pre_string***$i", $bb2html);
        $pre[$i] = str_replace("\r\n","\n",$pre_str);
        $i++;
    }

    $bb2html = str_replace("\n", ' :newline: ', $bb2html);

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

    if (ALLOW_BB_SMILIES) {
        $bb2html = getSmilie($bb2html);
    }

    // anchors and stuff..
    if (ALLOW_BB_IMAGES) {
        $bb2html = str_ireplace('[img]', '<img border="0" src="', $bb2html);
        $bb2html = str_ireplace('[/img]', '" alt="an image" />', $bb2html);
    } else {
        $bb2html = str_ireplace('[img]', '[' . _('No Images') . ']', $bb2html);
        $bb2html = str_ireplace('[/img]', '[/' . _('No Images') . ']', $bb2html);
    }

    $bb2html = preg_replace('/\[color=(#\w{6})\](.*)\[\/color\]/Ui', '<span style="color : \\1">\\2</span>', $bb2html);
    $bb2html = preg_replace('/\[url=([\w:\/\.\-&=\s\?#]+)\](.*)\[\/url\]/Ui', '<a rel="nofollow" target="_blank" href="\\1">\\2</a>', $bb2html);

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

    $bb2html = str_replace(' :newline: ', "\n", $bb2html);
    $bb2html = Text::breaker($bb2html);
    return $bb2html;
}

/**
 * Gets smilie graphics for bbcode
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
function getSmilie($bbcode)
{
    if (isset($GLOBALS['Smilie_Search'])) {
        $search = &$GLOBALS['Smilie_Search']['code'];
        $replace = &$GLOBALS['Smilie_Search']['img'];
    } else {
        $results = trim(file_get_contents(PHPWS_SOURCE_DIR . '/core/conf/smiles.pak'));
        if (empty($results)) {
            return $bbcode;
        }
        $smiles = explode("\n", $results);
        foreach ($smiles as $row) {
            $icon = explode('=+:', $row);

            if (count($icon) < 3) {
                continue;
            }
            $search[] = '@(?!<.*?)' . preg_quote($icon[2]) . '(?![^<>]*?>)@si';
            $replace[] = sprintf('<img src="images/core/smilies/%s" title="%s" alt="%s" />',
            $icon[0], $icon[1], $icon[1]);

        }

        $GLOBALS['Smilie_Search']['code'] = $search;
        $GLOBALS['Smilie_Search']['img'] = $replace;
    }

    $bbcode = preg_replace($search, $replace, $bbcode);
    return $bbcode;
}



?>