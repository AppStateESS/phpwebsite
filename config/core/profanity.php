<?php
/******************* Profanity Filter ********************/

/**
 * WARNING!! This file contains profanity (obviously). You may not want
 * to continue reading if this offends you.
 *
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


$censor = "*bleep*";
$words = array(
		   "[\s-\.]+cock"=>" " .$censor,
		   "mother\s?fucker"=>$censor,
		   "fuck"=>$censor,
		   "shit"=>$censor,
		   "asshole"=>$censor,
		   "cunt"=>$censor,
		   "nigger.*"=>"... I am a racist idiot! ",
		   "faggot.*"=>"... I have issues with my sexuality! "
		   );

define("FILTER_PROFANITY", TRUE);
define("PROFANE_WORDS", serialize($words));

?>