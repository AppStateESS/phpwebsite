<?php

/**
 * Loads functions that may be incompatible with older versions of php
 *
 * @author Matthew McNaney <matt at tux dot appstate dot edu>
 * @version $Id$
 */

$function_list = array('file_get_contents',
                       'file_put_contents',
                       'mime_content_type',
                       'html_entity_decode',
                       'scandir',
                       'str_ireplace',
                       'array_diff_key',
                       'stripos',
                       'clone'
                       );

if (version_compare(phpversion(), '5.0.0', '<')) {
    loadFunction($function_list);
 }

// Copy of PEAR's Compat function
function loadFunction($function)
{
    // Recursiveness
    if (is_array($function)) {
        $res = array();
        foreach ($function as $singlefunc) {
            $res[$singlefunc] = loadFunction($singlefunc);
        }

        return $res;
    }

    // Load function
    if (!function_exists($function)) {
        // edited for phpwebsite
        $file = sprintf('Compat/Function/%s.php', $function);

        if ((@include_once $file) !== false) {
            return true;
        }
    }

    return false;
}




?>