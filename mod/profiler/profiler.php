<?php
define('SOURCE_ADDRESS', 'http://localhost/fallout/');

function rand_profiler($profile_category, $template)
{
    $profile_category = preg_replace('/\W/', '', strip_tags($profile_category));
    $template         = preg_replace('/\W/', '', strip_tags($template));

    $directory =  sprintf('%s/index.php?module=profiler&user_cmd=random_profile&type=%s&template=%s',
                    SOURCE_ADDRESS, $profile_category, $template);

    echo file_get_contents($directory);
}

?>
