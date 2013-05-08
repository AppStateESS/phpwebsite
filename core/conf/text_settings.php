<?php

/**
 * WARNING!! This file helps phpwebsite filter profanity. As such, the
 * profanity is listed in this file. Stop reading now if this offends you.
 *
 * This file contains the default text settings for the PHPWS_Text class.
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

/**
 * These are the default tags that phpWebSite will allow from
 * form entries. If a tag is NOT on this list, it will be stripped
 * from the entry.
 */

define('PHPWS_ALLOWED_TAGS', '<pre>
<strong><b><a><i><u><ul><ol><li>
<table><tr><td><tbody><dd><dt><dl>
<p><br><div><span><blockquote><map><area>
<th><tt><img><pre><hr><h1><h2><h3><h4><h5><h6>
<fieldset><legend><code><em><iframe><embed>
<object><sup><sub><param><strike><del>');

/***************** HTML ENCODING ********************************
 * Before any user text passed into parseInput and out of
 * parseOutput is html encoded by default.
 * This is done as a security measure and to help with database
 * differences. If this is disabled (set to false) , most text
 * will be saved in an undecoded state.
 * Warning! Changing this at any time other than right after
 * installation could adversely affect your site.  Don't
 * change it unless you are sure of the consequences.
 */

define('ENCODE_PARSED_TEXT', true);


/**
 * The output text parsers will attempt to substitute breaks for newlines
 * in your content. This is a hold-over from the previous version
 * of phpWebSite which did not have wysiwyg. You can turn this feature
 * off by changing the below to FALSE.
 */
define('USE_BREAKER', false);

/*********************** FILTERS *******************************
 * phpWebSite filters outgoing text with a BB filter by default.
 * You can decide which filters, if any, phpWebSite uses.
 * If you use more than one filter, separate them with commas
 * (e.g. 'bb,wiki') in the TEXT_FILTERS define. Be aware that order
 * is important!
 */

// If FALSE, phpWebSite will not use any text filters to display text
// and will overwrite setting made by the module.
define('ALLOW_TEXT_FILTERS', true);

//define('TEXT_FILTERS', 'pear');
define('TEXT_FILTERS', 'bb');


/******************* RELATIVE ADDRESSING ***********************
 * The parseInput function the Text class will remove urls
 * and replace them with relative addresses if this option is TRUE
 * For example:
 * <a href='http://www.mysite.com/index.php?'>Home</a>
 * <img src='http://www.mysite.com/images/mymod/candy.jpg' />
 * will become
 * <a href='./index.php?'>Home</a>
 * <img src='./images/mymod/candy.jpg' />
 *
 * If for some reason you don't want this to happen, change to
 * FALSE
 */
define('MAKE_ADDRESSES_RELATIVE', TRUE);

// parseOutput/getPrint fixes bare anchors to become relative to
// the current page
define('FIX_ANCHORS', true);


/******************* COLLAPSE URLS ***********************/

// if true, parseOutput will collapse long urls into a shorter size
define('COLLAPSE_URLS', false);

// The collapsed url will ALWAYS contain the root address.
// You can't set this so low that the root is abbreviated.
define('COLLAPSE_LIMIT', 30);


/******************* Profanity Filter ********************
 *
 * The following words will be stripped automatically from any text
 * sent to the profanity filter function. The parseOutput function uses
 * this function if the filter is activated. You can deactivate the filtering
 * by changing FILTER_PROFANITY to FALSE.
 *
 * This is the array of bad words you want to removed from your
 * text. The array will used in a regular expression, so format
 * the words appropriately.
 * Example: To remove "cock" and not "peacock" we use:
 * "[\s-\.]+cock" which means 'only replace if there is a whitespace
 * character before.
 *
 * The value of the array is what you want to replace the word with.
 * We supply $censor as an example but you can format it however you wish.
 * Notice in the case of the 'N-word' we cut off all further information with
 * the '.*' suffix to the search.
 * Learn how to use regular expressions if you are confused.
 */


$censor = '*bleep*';
$words = array(
           '[\s-\.]+cock\s'   =>$censor,
           'mother\s?fucker'=>$censor,
           'fuck'           =>$censor,
           'shit'           =>$censor,
           'asshole'        =>$censor,
           'cunt'           =>$censor,
           'nigger.*'       =>'... I am a racist idiot! ',
           'faggot.*'       =>'... I have issues with my sexuality! '
           );

           define('ALLOW_PROFANITY', FALSE);
           define('PROFANE_WORDS', serialize($words));


           /**
            * These defines were originally in the bb filter itself. That would prevent
            * branches from setting their site as they wished.
            */

           // If TRUE, then 'smilies' will be parsed.
           define('ALLOW_BB_SMILIES', true);

           // If TRUE, users can post with the [img] tag
           define('ALLOW_BB_IMAGES', true);

           // Either "fieldset" or "blockquote"
           define('BBCODE_QUOTE_TYPE', 'fieldset');


           ?>