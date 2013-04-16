<?php
// $Id: mime_content_type.php,v 1.5 2007/04/17 10:09:56 arpad Exp $


/**
* Replace mime_content_type()
*
* You will need the `file` command installed and present in your $PATH. If
* `file` is not available, the type 'application/octet-stream' is returned
* for all files.
*
* @category   PHP
* @package    PHP_Compat
* @license    LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
* @link       http://php.net/function.mime_content_type
* @version    $Revision: 1.5 $
* @author     Ian Eure <ieure@php.net>
* @since      PHP 4.3.0
* @require    PHP 4.0.3 (escapeshellarg)
*/
function php_compat_mime_content_type($filename)
{
    // Sanity check
    if (!file_exists($filename)) {
        return false;
    }

    $filename = escapeshellarg($filename);
    $out = `file -iL $filename 2>/dev/null`;
    if (empty($out)) {
        return 'application/octet-stream';
    }

    // Strip off filename
    $t = substr($out, strpos($out, ':') + 2);

    if (strpos($t, ';') !== false) {
        // Strip MIME parameters
        $t = substr($t, 0, strpos($t, ';'));
    }

    // Strip any remaining whitespace
    return trim($t);
}


// Define
if (!function_exists('mime_content_type')) {
    function mime_content_type($filename)
    {
        return php_compat_mime_content_type($filename);
    }
}
