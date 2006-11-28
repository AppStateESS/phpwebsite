<?php

/**
 * WARNING!! This file helps phpwebsite filter profanity. As such, the
 * profanity is listed in this file. Stop reading now if this offends you.
 *
 * This file contains the default text settings for the PHPWS_Text class.
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
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
<p><br><div><span><blockquote>
<th><tt><img><pre><hr><h1><h2><h3><h4>
<fieldset><legend><code>
');

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


/********************* BBCode ***************************/
// Set whether you want to allow bbcode to get parsed by default.
define('ALLOW_BB_CODE', TRUE);
// If TRUE, then 'smilies' will be parsed. The above MUST be TRUE.
define('ALLOW_BB_SMILIES', TRUE);

// If TRUE, users can post with the [img] tag
define('ALLOW_BB_IMAGES', FALSE);

// Either "fieldset" or "blockquote"
define('BBCODE_QUOTE_TYPE', 'fieldset');

// parseOutput/getPrint fixes bare anchors to become relative to
// the current page
define('FIX_ANCHORS', true);


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
		   '[\s-\.]+cock'   =>$censor,
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


?>