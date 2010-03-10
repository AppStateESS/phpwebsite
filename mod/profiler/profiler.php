<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 *
 * Call this function from your site to access a profile.
 * The source address must be where you are grabbing the profile from.
 * The category is the number of the profile division.
 */

define('SOURCE_ADDRESS', 'http://mysite/');

function rand_profiler($profile_division, $template)
{
    $profile_division = preg_replace('/\W/', '', strip_tags($profile_division));
    $template         = preg_replace('/\W/', '', strip_tags($template));

    $directory =  sprintf('%s/index.php?module=profiler&user_cmd=random_profile&type=%s&template=%s',
    SOURCE_ADDRESS, $profile_division, $template);

    echo file_get_contents($directory);
}

?>
